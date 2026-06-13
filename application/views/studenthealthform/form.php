<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Health Form</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;flex-direction:column}
.page-header{background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.header-img-wrap{width:100%;max-height:160px;overflow:hidden;display:flex;justify-content:center;background:#fff}
.header-img-wrap img{width:100%;max-height:160px;object-fit:contain}

/* Progress bar */
.progress-wrap{background:#fff;border-bottom:1px solid #eee;padding:12px 16px;position:sticky;top:0;z-index:100}
.progress-info{display:flex;justify-content:space-between;font-size:12px;color:#666;margin-bottom:6px}
.progress-info span{font-weight:700;color:#e53935}
.progress-bar-bg{background:#f0e0e0;border-radius:8px;height:6px}
.progress-bar-fill{background:linear-gradient(90deg,#e53935,#b71c1c);height:6px;border-radius:8px;transition:width .4s}

.page-body{flex:1;padding:24px 16px 80px;max-width:680px;margin:0 auto;width:100%}

/* Steps */
.step{display:none}
.step.active{display:block}
.step-header{margin-bottom:20px}
.step-header h2{font-size:18px;color:#1a1a2e;font-weight:700;display:flex;align-items:center;gap:10px}
.step-icon{width:36px;height:36px;background:linear-gradient(135deg,#e53935,#b71c1c);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.step-icon svg{width:18px;height:18px;fill:#fff}
.step-header p{font-size:13px;color:#888;margin-top:6px;margin-left:46px}

/* Cards */
.card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:20px;margin-bottom:16px}
.prefilled-banner{background:#e8f5e9;border:1px solid #c8e6c9;border-radius:8px;padding:10px 14px;font-size:12px;color:#2e7d32;margin-bottom:16px;display:flex;align-items:center;gap:8px}

/* Form fields */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.form-row.full{grid-template-columns:1fr}
.form-row.three{grid-template-columns:1fr 1fr 1fr}
.field{display:flex;flex-direction:column;gap:4px}
.field label{font-size:12px;font-weight:600;color:#555}
.field input,.field select,.field textarea{padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;outline:none;transition:border-color .2s;font-family:inherit}
.field input:focus,.field select:focus,.field textarea:focus{border-color:#e53935}
.field input[readonly]{background:#f5f5f5;color:#666}
.field textarea{resize:vertical;min-height:70px}
.readonly-badge{font-size:10px;background:#e0e0e0;color:#666;padding:2px 6px;border-radius:4px;margin-left:6px;vertical-align:middle}

/* Yes/No toggles */
.toggle-group{display:flex;flex-direction:column;gap:10px}
.toggle-row{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border:1.5px solid #e0e0e0;border-radius:10px;background:#fafafa}
.toggle-row.answered{border-color:#c8e6c9;background:#f1f8e9}
.toggle-label{font-size:14px;color:#333;font-weight:500}
.toggle-btns{display:flex;gap:6px}
.toggle-btn{padding:6px 16px;border-radius:20px;border:1.5px solid #ddd;background:#fff;font-size:13px;cursor:pointer;font-weight:600;transition:all .15s}
.toggle-btn.yes.active{background:#4caf50;border-color:#4caf50;color:#fff}
.toggle-btn.no.active{background:#ef5350;border-color:#ef5350;color:#fff}

/* Checkboxes */
.check-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.check-item{display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px solid #e0e0e0;border-radius:8px;cursor:pointer;transition:all .15s;user-select:none}
.check-item:hover{border-color:#e53935;background:#fdecea}
.check-item input[type=checkbox]{width:16px;height:16px;accent-color:#e53935;cursor:pointer}
.check-item.checked{border-color:#e53935;background:#fdecea}
.check-item span{font-size:14px;color:#333}

/* Sibling table */
.sibling-table{width:100%;border-collapse:collapse;font-size:13px;margin-top:10px}
.sibling-table th{background:#f5f5f5;padding:8px 10px;text-align:left;font-weight:600;color:#555;border-bottom:2px solid #eee}
.sibling-table td{padding:8px 10px;border-bottom:1px solid #f0f0f0}

/* Radio style for vaccine */
.radio-group{display:flex;gap:10px;flex-wrap:wrap}
.radio-card{padding:10px 18px;border:1.5px solid #e0e0e0;border-radius:8px;cursor:pointer;font-size:14px;transition:all .15s}
.radio-card input{display:none}
.radio-card.active{border-color:#e53935;background:#fdecea;color:#e53935;font-weight:700}

/* Nav buttons */
.nav-bar{position:fixed;bottom:0;left:50%;transform:translateX(-50%);padding:12px 16px;display:flex;gap:10px;justify-content:center;width:100%;max-width:680px;background:transparent;border-top:none;pointer-events:none}
.nav-bar button{pointer-events:all}
.btn-back{flex:1;padding:13px;border:2px solid #e0e0e0;background:#fff;border-radius:10px;font-size:15px;font-weight:600;color:#666;cursor:pointer;max-width:140px}
.btn-next{flex:2;padding:13px;background:linear-gradient(135deg,#e53935,#b71c1c);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;max-width:300px}
.btn-next:disabled{opacity:.6;cursor:not-allowed}
.btn-submit{flex:2;padding:13px;background:linear-gradient(135deg,#43a047,#1b5e20);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;max-width:300px}

@media(max-width:480px){
  .form-row{grid-template-columns:1fr}
  .form-row.three{grid-template-columns:1fr 1fr}
  .check-grid{grid-template-columns:1fr}
}
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

<div class="progress-wrap">
    <div class="progress-info">
        <span id="step-label">Step 1 of 11</span>
        <span id="step-title-top">Student Information</span>
    </div>
    <div class="progress-bar-bg"><div class="progress-bar-fill" id="prog-bar" style="width:9%"></div></div>
</div>

<div class="page-body">

<!-- STEP 1: Student Info -->
<div class="step active" id="step-1">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v2h20v-2c0-3.3-6.7-5-10-5z"/></svg></div>Student Information</h2>
        <p>Pre-filled from school records. Some fields are editable if needed.</p>
    </div>
    <div class="prefilled-banner"><svg width="16" height="16" fill="#2e7d32" viewBox="0 0 24 24"><path d="M9 16.2l-3.5-3.5-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg> Fields below are pre-filled from our records.</div>
    <div class="card">
        <div class="form-row">
            <div class="field"><label>Student Name</label><input type="text" id="f-name" readonly></div>
            <div class="field"><label>Admission No</label><input type="text" id="f-admission-no" readonly></div>
        </div>
        <div class="form-row three">
            <div class="field"><label>EMIS No.</label><input type="text" id="f-emis" readonly></div>
            <div class="field"><label>Class &amp; Section</label><input type="text" id="f-class" readonly></div>
            <div class="field"><label>Date of Birth</label><input type="text" id="f-dob" readonly></div>
        </div>
        <div class="form-row three">
            <div class="field"><label>Gender</label><input type="text" id="f-gender" readonly></div>
            <div class="field"><label>Blood Group</label><input type="text" id="f-blood" readonly></div>
            <div class="field"><label>Admission Date</label><input type="text" id="f-admission-date" readonly></div>
        </div>
        <div class="form-row full">
            <div class="field"><label>Previous School (if any)</label><input type="text" id="f-prev-school" placeholder="Previous school name"></div>
        </div>
    </div>
    <input type="hidden" id="h-student-id">
</div>

<!-- STEP 2: Sibling Details -->
<div class="step" id="step-2">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>Sibling Details</h2>
        <p>Does this student have any siblings studying in this school?</p>
    </div>
    <div class="card" id="sibling-auto-card" style="display:none">
        <div class="prefilled-banner"><svg width="16" height="16" fill="#2e7d32" viewBox="0 0 24 24"><path d="M9 16.2l-3.5-3.5-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg> We found sibling(s) enrolled in this school:</div>
        <table class="sibling-table">
            <thead><tr><th>Name</th><th>Adm. No</th><th>Class</th></tr></thead>
            <tbody id="sibling-tbody"></tbody>
        </table>
    </div>
    <div class="card">
        <div class="toggle-group">
            <div class="toggle-row" id="sibling-toggle-row">
                <span class="toggle-label">Does this student have sibling(s) in this school?</span>
                <div class="toggle-btns">
                    <button type="button" class="toggle-btn yes" onclick="setToggle('has_sibling','yes',this)">Yes</button>
                    <button type="button" class="toggle-btn no" onclick="setToggle('has_sibling','no',this)">No</button>
                </div>
            </div>
        </div>
        <div id="sibling-details-area" style="display:none;margin-top:14px">
            <div class="field"><label>Sibling details (Name, Class, Section)</label><textarea id="f-sibling-details" rows="2" placeholder="e.g. Ravi Kumar, Grade 5 A"></textarea></div>
        </div>
    </div>
    <input type="hidden" id="h-has-sibling" value="">
</div>

<!-- STEP 3: Parent/Guardian Details -->
<div class="step" id="step-3">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg></div>Parent / Guardian Details</h2>
        <p>Pre-filled from school records. Please verify and correct if needed.</p>
    </div>
    <div class="prefilled-banner"><svg width="16" height="16" fill="#2e7d32" viewBox="0 0 24 24"><path d="M9 16.2l-3.5-3.5-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg> Fields below are pre-filled. Tap to edit.</div>
    <div class="card">
        <p style="font-size:13px;font-weight:700;color:#555;margin-bottom:12px;">Father's Details</p>
        <div class="form-row">
            <div class="field"><label>Father's Name</label><input type="text" id="f-father-name" placeholder="Father's full name"></div>
            <div class="field"><label>Occupation</label><input type="text" id="f-father-occ" placeholder="Occupation"></div>
        </div>
        <div class="form-row full"><div class="field"><label>Mobile Number</label><input type="tel" id="f-father-phone" placeholder="Mobile number"></div></div>
    </div>
    <div class="card">
        <p style="font-size:13px;font-weight:700;color:#555;margin-bottom:12px;">Mother's Details</p>
        <div class="form-row">
            <div class="field"><label>Mother's Name</label><input type="text" id="f-mother-name" placeholder="Mother's full name"></div>
            <div class="field"><label>Occupation</label><input type="text" id="f-mother-occ" placeholder="Occupation"></div>
        </div>
        <div class="form-row full"><div class="field"><label>Mobile Number</label><input type="tel" id="f-mother-phone" placeholder="Mobile number"></div></div>
    </div>
    <div class="card">
        <div class="form-row full"><div class="field"><label>Residential Address</label><textarea id="f-address" rows="3" placeholder="Full residential address"></textarea></div></div>
    </div>
</div>

<!-- STEP 4: Emergency Contact -->
<div class="step" id="step-4">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg></div>Emergency Contact</h2>
        <p>Someone we can contact in case of emergency (other than parents if possible).</p>
    </div>
    <div class="card">
        <div class="form-row">
            <div class="field"><label>Contact Person Name <span style="color:#e53935">*</span></label><input type="text" id="f-emg-name" placeholder="Full name"></div>
            <div class="field"><label>Relationship to Student</label><input type="text" id="f-emg-relation" placeholder="e.g. Uncle, Grandparent"></div>
        </div>
        <div class="form-row">
            <div class="field"><label>Mobile Number <span style="color:#e53935">*</span></label><input type="tel" id="f-emg-mobile" placeholder="Primary mobile"></div>
            <div class="field"><label>Alternate Mobile</label><input type="tel" id="f-emg-alt" placeholder="Alternate mobile"></div>
        </div>
    </div>
</div>

<!-- STEP 5: General Health -->
<div class="step" id="step-5">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>General Health Information</h2>
        <p>Please answer each question for your child.</p>
    </div>
    <div class="card">
        <div class="toggle-group">
            <div class="toggle-row"><span class="toggle-label">Does your child wear spectacles?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('wears_spectacles','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('wears_spectacles','no',this)">No</button></div></div>
            <div class="toggle-row"><span class="toggle-label">Vision-related difficulties?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('vision_difficulty','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('vision_difficulty','no',this)">No</button></div></div>
            <div class="toggle-row"><span class="toggle-label">Hearing difficulties?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('hearing_difficulty','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('hearing_difficulty','no',this)">No</button></div></div>
            <div class="toggle-row"><span class="toggle-label">Speech-related difficulties?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('speech_difficulty','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('speech_difficulty','no',this)">No</button></div></div>
            <div class="toggle-row"><span class="toggle-label">Requires special assistance?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('special_assistance','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('special_assistance','no',this)">No</button></div></div>
        </div>
    </div>
    <div class="card" id="special-assist-details" style="display:none">
        <div class="field"><label>Please describe the special assistance required</label><textarea id="f-special-assist-details" rows="3" placeholder="Describe the assistance needed..."></textarea></div>
    </div>
</div>

<!-- STEP 6: Allergies -->
<div class="step" id="step-6">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg></div>Allergies</h2>
        <p>Select all that apply for your child.</p>
    </div>
    <div class="card">
        <div class="check-grid">
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="a-food" value="1"><span>🍔 Food</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="a-medication" value="1"><span>💊 Medication</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="a-insect" value="1"><span>🐝 Insect Bites / Stings</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="a-dust" value="1"><span>🌿 Dust / Pollen</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="a-other-allergy" value="1"><span>➕ Other</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="a-none" value="1"><span>✅ No Known Allergies</span></label>
        </div>
        <div style="margin-top:14px">
            <div class="field"><label>Details (if any)</label><textarea id="f-allergy-details" rows="2" placeholder="Describe specific allergens or reactions..."></textarea></div>
        </div>
    </div>
</div>

<!-- STEP 7: Medical History -->
<div class="step" id="step-7">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></div>Medical History</h2>
        <p>Please tick all conditions that apply to your child.</p>
    </div>
    <div class="card">
        <div class="check-grid">
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-asthma"><span>Asthma</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-diabetes"><span>Diabetes</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-epilepsy"><span>Epilepsy / Seizures</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-heart"><span>Heart Condition</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-kidney"><span>Kidney Disorder</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-thyroid"><span>Thyroid Disorder</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-physical"><span>Physical Disability</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-learning"><span>Learning Difficulty</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-vision"><span>Vision Impairment</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-hearing"><span>Hearing Impairment</span></label>
            <label class="check-item" onclick="toggleCheck(this)"><input type="checkbox" id="m-other-med"><span>Other Medical Condition</span></label>
        </div>
        <div style="margin-top:14px">
            <div class="field"><label>Details</label><textarea id="f-med-details" rows="2" placeholder="Provide details of any conditions ticked above..."></textarea></div>
        </div>
    </div>
</div>

<!-- STEP 8: Surgery & Medications -->
<div class="step" id="step-8">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M7 5h10v2H7zm0 4h10v2H7zm0 4h7v2H7zm12 5l-4-4H5V3h14v15zm-2-2V5H7v14h7l3 3z"/></svg></div>Surgery &amp; Current Medications</h2>
        <p>Please provide information about any past surgeries and current medications.</p>
    </div>
    <div class="card">
        <div class="toggle-group">
            <div class="toggle-row"><span class="toggle-label">Has your child undergone any surgery or major hospitalization?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('surgery_history','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('surgery_history','no',this)">No</button></div></div>
        </div>
        <div id="surgery-details-area" style="display:none;margin-top:14px">
            <div class="field"><label>Please provide details</label><textarea id="f-surgery-details" rows="3" placeholder="Describe the surgery/hospitalization, year, hospital..."></textarea></div>
        </div>
    </div>
    <div class="card">
        <div class="toggle-group">
            <div class="toggle-row"><span class="toggle-label">Is your child currently taking any medication?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('on_medication','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('on_medication','no',this)">No</button></div></div>
        </div>
        <div id="medication-area" style="display:none;margin-top:14px">
            <div class="field"><label>Please list the medications</label><textarea id="f-medications" rows="3" placeholder="Name of medications, dosage, frequency..."></textarea></div>
        </div>
    </div>
</div>

<!-- STEP 9: Immunization & PE -->
<div class="step" id="step-9">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M13 2.05v2.02c3.95.49 7 3.85 7 7.93 0 3.21-1.81 6-4.72 7.28L13 17v5h5l-1.22-1.22C19.91 19.07 22 15.76 22 12c0-5.18-3.95-9.45-9-9.95zM11 2.05C5.95 2.55 2 6.82 2 12c0 3.76 2.09 7.07 5.22 8.78L6 22h5v-5l-2.28 2.28C6.81 18 5 15.21 5 12c0-4.08 3.05-7.44 7-7.93V2.05z"/></svg></div>Immunization &amp; Physical Education</h2>
        <p>Vaccination status and fitness for school activities.</p>
    </div>
    <div class="card">
        <p style="font-size:13px;font-weight:600;color:#555;margin-bottom:12px;">Vaccinations up to date?</p>
        <div class="radio-group" id="vaccine-group">
            <label class="radio-card"><input type="radio" name="vaccine" value="yes" onchange="selectRadio(this,'vaccine-group')"> Yes</label>
            <label class="radio-card"><input type="radio" name="vaccine" value="no" onchange="selectRadio(this,'vaccine-group')"> No</label>
            <label class="radio-card"><input type="radio" name="vaccine" value="not_sure" onchange="selectRadio(this,'vaccine-group')"> Not Sure</label>
        </div>
        <div style="margin-top:14px">
            <div class="field"><label>Remarks (optional)</label><textarea id="f-vaccine-remarks" rows="2" placeholder="Any vaccination notes..."></textarea></div>
        </div>
    </div>
    <div class="card">
        <div class="toggle-group">
            <div class="toggle-row"><span class="toggle-label">Is your child medically fit for Physical Education, sports and school activities?</span><div class="toggle-btns"><button type="button" class="toggle-btn yes" onclick="setToggle('pe_fit','yes',this)">Yes</button><button type="button" class="toggle-btn no" onclick="setToggle('pe_fit','no',this)">No</button></div></div>
        </div>
        <div id="pe-restrictions-area" style="display:none;margin-top:14px">
            <div class="field"><label>Restrictions</label><textarea id="f-pe-restrictions" rows="2" placeholder="Describe any PE/sports restrictions..."></textarea></div>
        </div>
    </div>
</div>

<!-- STEP 10: Special Instructions -->
<div class="step" id="step-10">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11zM8 15.01l1.41 1.41L11 14.84l1.59 1.58L14 15.01l-1.59-1.59L14 11.84l-1.41-1.41L11 11.99l-1.59-1.57L8 11.84l1.58 1.58z"/></svg></div>Special Health Care Instructions</h2>
        <p>Dietary restrictions, recurring illnesses, behavioural concerns, or any other medical precautions.</p>
    </div>
    <div class="card">
        <div class="field">
            <label>Special instructions for the school</label>
            <textarea id="f-special-instructions" rows="5" placeholder="e.g. Child is lactose intolerant. Must avoid dairy. Has recurring migraines — please allow rest in a quiet room..."></textarea>
        </div>
    </div>
</div>

<!-- STEP 11: Declaration -->
<div class="step" id="step-11">
    <div class="step-header">
        <h2><div class="step-icon"><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>Parent / Guardian Declaration</h2>
        <p>Please read and confirm the declaration below before submitting.</p>
    </div>
    <div class="card">
        <p style="font-size:13px;color:#555;line-height:1.7;margin-bottom:16px;">I hereby declare that the information provided in this form is <strong>true and complete</strong> to the best of my knowledge. I undertake to inform the school of any changes in my child's health condition or emergency contact details.<br><br>I authorize the school to administer <strong>first aid</strong> and seek emergency medical treatment for my child whenever necessary and when immediate contact with the parent/guardian is not possible.</p>
        <div class="form-row">
            <div class="field"><label>Parent / Guardian Name <span style="color:#e53935">*</span></label><input type="text" id="f-declaration-name" placeholder="Your full name"></div>
            <div class="field"><label>Date</label><input type="date" id="f-declaration-date"></div>
        </div>
    </div>
    <div id="submit-error" style="background:#fdecea;color:#c62828;border-radius:8px;padding:12px 14px;font-size:13px;margin-top:12px;display:none"></div>
</div>

</div><!-- /.page-body -->

<!-- Fixed nav bar -->
<div class="nav-bar">
    <button class="btn-back" id="btn-back" onclick="prevStep()" style="display:none">&larr; Back</button>
    <button class="btn-next" id="btn-next" onclick="nextStep()">Next &rarr;</button>
    <button class="btn-submit" id="btn-submit" style="display:none" onclick="submitForm()">Submit &amp; Get PDF</button>
</div>

<script>
var BASE_URL = '<?php echo site_url(); ?>';
var TOTAL_STEPS = 11;
var currentStep = 1;
var toggleState = {};
var shfData = null;

var STEP_TITLES = ['Student Information','Sibling Details','Parent / Guardian Details',
    'Emergency Contact','General Health','Allergies','Medical History',
    'Surgery & Medications','Immunization & PE','Special Instructions','Declaration'];

// Load data from sessionStorage
try { shfData = JSON.parse(sessionStorage.getItem('shf_data')); } catch(e){}
if (!shfData || !shfData.student) { window.location.href = BASE_URL + 'studenthealthform'; }

window.addEventListener('load', function() {
    prefillForm();
    updateNav();
});

function prefillForm() {
    var s = shfData.student;
    var r = shfData.record || {};
    var fullName = [s.firstname, s.middlename, s.lastname].filter(Boolean).join(' ');
    setVal('f-name', fullName);
    setVal('f-admission-no', s.admission_no);
    setVal('f-emis', s.emis_num || '');
    setVal('f-class', [s.class, s.section].filter(Boolean).join(' - ') || '');
    setVal('f-dob', s.dob ? formatDate(s.dob) : '');
    setVal('f-gender', s.gender || '');
    setVal('f-blood', s.blood_group || '');
    setVal('f-admission-date', s.admission_date ? formatDate(s.admission_date) : '');
    setVal('f-prev-school', s.previous_school || '');
    document.getElementById('h-student-id').value = s.id;

    // Siblings
    if (shfData.siblings && shfData.siblings.length > 0) {
        document.getElementById('sibling-auto-card').style.display = 'block';
        var tbody = document.getElementById('sibling-tbody');
        shfData.siblings.forEach(function(sib) {
            var row = '<tr><td>' + esc(sib.firstname + ' ' + sib.lastname) + '</td><td>' + esc(sib.admission_no) + '</td><td>' + esc((sib.class||'') + ' ' + (sib.section||'')) + '</td></tr>';
            tbody.innerHTML += row;
        });
        toggleState['has_sibling'] = 'yes';
        document.getElementById('h-has-sibling').value = 'yes';
        document.getElementById('sibling-details-area').style.display = 'block';
        highlightToggle('has_sibling', 'yes');
    }

    // Parent details
    setVal('f-father-name', s.father_name || '');
    setVal('f-father-occ', s.father_occupation || '');
    setVal('f-father-phone', s.father_phone || '');
    setVal('f-mother-name', s.mother_name || '');
    setVal('f-mother-occ', s.mother_occupation || '');
    setVal('f-mother-phone', s.mother_phone || '');
    setVal('f-address', s.current_address || '');

    // Pre-fill from existing record
    if (r.emergency_contact_name) setVal('f-emg-name', r.emergency_contact_name);
    if (r.emergency_contact_relation) setVal('f-emg-relation', r.emergency_contact_relation);
    if (r.emergency_contact_mobile) setVal('f-emg-mobile', r.emergency_contact_mobile);
    if (r.emergency_contact_alt_mobile) setVal('f-emg-alt', r.emergency_contact_alt_mobile);
    if (r.special_assistance_details) setVal('f-special-assist-details', r.special_assistance_details);
    if (r.allergy_details) setVal('f-allergy-details', r.allergy_details);
    if (r.med_details) setVal('f-med-details', r.med_details);
    if (r.surgery_details) setVal('f-surgery-details', r.surgery_details);
    if (r.current_medications) setVal('f-medications', r.current_medications);
    if (r.vaccination_remarks) setVal('f-vaccine-remarks', r.vaccination_remarks);
    if (r.pe_restrictions) setVal('f-pe-restrictions', r.pe_restrictions);
    if (r.special_health_instructions) setVal('f-special-instructions', r.special_health_instructions);
    if (r.sibling_details) setVal('f-sibling-details', r.sibling_details);
    if (r.declaration_name) setVal('f-declaration-name', r.declaration_name);

    var today = new Date().toISOString().split('T')[0];
    setVal('f-declaration-date', r.declaration_date || today);
}

function setVal(id, val) { var el = document.getElementById(id); if(el) el.value = val || ''; }
function formatDate(d) { if(!d) return ''; var p = d.split('-'); return p.length===3 ? p[2]+'/'+p[1]+'/'+p[0] : d; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function setToggle(key, val, btn) {
    toggleState[key] = val;
    var row = btn.closest('.toggle-row');
    row.classList.add('answered');
    row.querySelectorAll('.toggle-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    // Show/hide conditional sections
    if (key === 'has_sibling') {
        document.getElementById('h-has-sibling').value = val;
        document.getElementById('sibling-details-area').style.display = (val==='yes') ? 'block' : 'none';
    }
    if (key === 'special_assistance') {
        document.getElementById('special-assist-details').style.display = (val==='yes') ? 'block' : 'none';
    }
    if (key === 'surgery_history') {
        document.getElementById('surgery-details-area').style.display = (val==='yes') ? 'block' : 'none';
    }
    if (key === 'on_medication') {
        document.getElementById('medication-area').style.display = (val==='yes') ? 'block' : 'none';
    }
    if (key === 'pe_fit') {
        document.getElementById('pe-restrictions-area').style.display = (val==='no') ? 'block' : 'none';
    }
}

function highlightToggle(key, val) {
    // Pre-highlight without a button ref
    document.querySelectorAll('.toggle-btn').forEach(function(b) {
        if (b.getAttribute('onclick') && b.getAttribute('onclick').includes("'"+key+"'") && b.getAttribute('onclick').includes("'"+val+"'")) {
            b.classList.add('active');
            b.closest('.toggle-row').classList.add('answered');
        }
    });
}

function toggleCheck(label) {
    var cb = label.querySelector('input[type=checkbox]');
    label.classList.toggle('checked', cb.checked);
}

function selectRadio(radio, groupId) {
    document.getElementById(groupId).querySelectorAll('.radio-card').forEach(function(c){ c.classList.remove('active'); });
    radio.closest('.radio-card').classList.add('active');
}

function nextStep() {
    if (!validateStep(currentStep)) return;
    if (currentStep < TOTAL_STEPS) {
        showStep(currentStep + 1);
    }
}
function prevStep() {
    if (currentStep > 1) showStep(currentStep - 1);
}

function showStep(n) {
    document.getElementById('step-' + currentStep).classList.remove('active');
    currentStep = n;
    document.getElementById('step-' + currentStep).classList.add('active');
    updateNav();
    window.scrollTo(0, 0);
}

function updateNav() {
    var pct = Math.round((currentStep / TOTAL_STEPS) * 100);
    document.getElementById('prog-bar').style.width = pct + '%';
    document.getElementById('step-label').textContent = 'Step ' + currentStep + ' of ' + TOTAL_STEPS;
    document.getElementById('step-title-top').textContent = STEP_TITLES[currentStep - 1];
    document.getElementById('btn-back').style.display = currentStep > 1 ? 'block' : 'none';
    var isLast = currentStep === TOTAL_STEPS;
    document.getElementById('btn-next').style.display   = isLast ? 'none' : 'block';
    document.getElementById('btn-submit').style.display = isLast ? 'block' : 'none';
}

function isValidMobile(v) { return /^\d{10}$/.test(v); }

function validateStep(step) {
    if (step === 4) {
        var name = document.getElementById('f-emg-name').value.trim();
        var mob  = document.getElementById('f-emg-mobile').value.trim();
        var alt  = document.getElementById('f-emg-alt').value.trim();
        if (!name) { alert('Please enter the emergency contact name.'); return false; }
        if (!mob)  { alert('Please enter the emergency contact mobile number.'); return false; }
        if (!isValidMobile(mob)) { alert('Mobile Number must be exactly 10 digits.'); document.getElementById('f-emg-mobile').focus(); return false; }
        if (alt && !isValidMobile(alt)) { alert('Alternate Mobile must be exactly 10 digits.'); document.getElementById('f-emg-alt').focus(); return false; }
    }
    if (step === 11) {
        if (!document.getElementById('f-declaration-name').value.trim()) {
            document.getElementById('submit-error').textContent = 'Please enter your name in the declaration.';
            document.getElementById('submit-error').style.display = 'block';
            return false;
        }
    }
    return true;
}

function submitForm() {
    if (!validateStep(11)) return;
    var btn = document.getElementById('btn-submit');
    btn.disabled = true; btn.textContent = 'Submitting...';
    document.getElementById('submit-error').style.display = 'none';

    var fd = new FormData();
    fd.append('student_id',                   document.getElementById('h-student-id').value);
    fd.append('emergency_contact_name',        document.getElementById('f-emg-name').value.trim());
    fd.append('emergency_contact_relation',    document.getElementById('f-emg-relation').value.trim());
    fd.append('emergency_contact_mobile',      document.getElementById('f-emg-mobile').value.trim());
    fd.append('emergency_contact_alt_mobile',  document.getElementById('f-emg-alt').value.trim());
    fd.append('wears_spectacles',   toggleState['wears_spectacles']==='yes' ? 1 : 0);
    fd.append('vision_difficulty',  toggleState['vision_difficulty']==='yes' ? 1 : 0);
    fd.append('hearing_difficulty', toggleState['hearing_difficulty']==='yes' ? 1 : 0);
    fd.append('speech_difficulty',  toggleState['speech_difficulty']==='yes' ? 1 : 0);
    fd.append('special_assistance', toggleState['special_assistance']==='yes' ? 1 : 0);
    fd.append('special_assistance_details', document.getElementById('f-special-assist-details').value);
    fd.append('allergy_food',        document.getElementById('a-food').checked ? 1 : 0);
    fd.append('allergy_medication',  document.getElementById('a-medication').checked ? 1 : 0);
    fd.append('allergy_insect',      document.getElementById('a-insect').checked ? 1 : 0);
    fd.append('allergy_dust',        document.getElementById('a-dust').checked ? 1 : 0);
    fd.append('allergy_other',       document.getElementById('a-other-allergy').checked ? 1 : 0);
    fd.append('allergy_none',        document.getElementById('a-none').checked ? 1 : 0);
    fd.append('allergy_details',     document.getElementById('f-allergy-details').value);
    fd.append('med_asthma',          document.getElementById('m-asthma').checked ? 1 : 0);
    fd.append('med_diabetes',        document.getElementById('m-diabetes').checked ? 1 : 0);
    fd.append('med_epilepsy',        document.getElementById('m-epilepsy').checked ? 1 : 0);
    fd.append('med_heart',           document.getElementById('m-heart').checked ? 1 : 0);
    fd.append('med_kidney',          document.getElementById('m-kidney').checked ? 1 : 0);
    fd.append('med_thyroid',         document.getElementById('m-thyroid').checked ? 1 : 0);
    fd.append('med_physical_disability',  document.getElementById('m-physical').checked ? 1 : 0);
    fd.append('med_learning_difficulty',  document.getElementById('m-learning').checked ? 1 : 0);
    fd.append('med_vision_impairment',    document.getElementById('m-vision').checked ? 1 : 0);
    fd.append('med_hearing_impairment',   document.getElementById('m-hearing').checked ? 1 : 0);
    fd.append('med_other',                document.getElementById('m-other-med').checked ? 1 : 0);
    fd.append('med_details',              document.getElementById('f-med-details').value);
    fd.append('surgery_history',    toggleState['surgery_history']==='yes' ? 1 : 0);
    fd.append('surgery_details',    document.getElementById('f-surgery-details').value);
    fd.append('current_medications',document.getElementById('f-medications').value);
    var vaccine = document.querySelector('input[name="vaccine"]:checked');
    fd.append('vaccinations_uptodate', vaccine ? vaccine.value : '');
    fd.append('vaccination_remarks',   document.getElementById('f-vaccine-remarks').value);
    fd.append('pe_fit',              toggleState['pe_fit']==='no' ? 0 : 1);
    fd.append('pe_restrictions',     document.getElementById('f-pe-restrictions').value);
    fd.append('special_health_instructions', document.getElementById('f-special-instructions').value);
    fd.append('has_sibling',         toggleState['has_sibling']==='yes' ? 1 : 0);
    fd.append('sibling_details',     document.getElementById('f-sibling-details') ? document.getElementById('f-sibling-details').value : '');
    fd.append('declaration_name',    document.getElementById('f-declaration-name').value.trim());
    fd.append('declaration_date',    document.getElementById('f-declaration-date').value);

    fetch(BASE_URL + 'studenthealthform/submit', { method:'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (res.status === '1') {
                sessionStorage.setItem('shf_token', res.token);
                sessionStorage.setItem('shf_pdf_url', res.pdf_url);
                sessionStorage.removeItem('shf_data');
                window.location.href = BASE_URL + 'studenthealthform/confirmed';
            } else {
                btn.disabled = false; btn.textContent = 'Submit & Get PDF';
                document.getElementById('submit-error').textContent = res.msg || 'Submission failed. Please try again.';
                document.getElementById('submit-error').style.display = 'block';
            }
        })
        .catch(function() {
            btn.disabled = false; btn.textContent = 'Submit & Get PDF';
            document.getElementById('submit-error').textContent = 'Network error. Please try again.';
            document.getElementById('submit-error').style.display = 'block';
        });
}
</script>
</body>
</html>
