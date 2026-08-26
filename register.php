<?php
include('connection.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — E-Gov Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:   #004aad;
            --secondary: #0077ff;
            --accent:    #ffd700;
            --text-dark: #1a1a2e;
            --text-mid:  #444;
            --text-light:#777;
            --bg:        #f4f7fb;
            --white:     #ffffff;
            --error:     #e53935;
            --success:   #2e7d32;
            --radius:    16px;
            --shadow-sm: 0 2px 8px rgba(0,74,173,0.10);
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            overflow-y: auto;
        }

        /* ═══════════════════════════════
           LEFT PANEL — Brand
        ═══════════════════════════════ */
        .brand-panel {
            width: 40%;
            background: linear-gradient(145deg, #003a8c 0%, #0055cc 45%, #0077ff 100%);
            position: sticky;
            top: 0;
            height: 100vh;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 52px;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.10;
            animation: drift 9s ease-in-out infinite;
        }
        .shape-1 { width:380px; height:380px; background:white; top:-100px; right:-120px; animation-delay:0s; }
        .shape-2 { width:220px; height:220px; background:var(--accent); bottom:-60px; left:-50px; animation-delay:-4s; }
        .shape-3 { width:140px; height:140px; background:white; bottom:140px; right:60px; animation-delay:-6s; opacity:0.07; }
        .shape-4 { width:70px; height:70px; background:var(--accent); top:180px; left:20px; animation-delay:-2s; opacity:0.18; border-radius:14px; transform:rotate(25deg); }
        .shape-5 { width:100px; height:100px; background:white; top:60%; right:20%; animation-delay:-7s; opacity:0.06; }

        @keyframes drift {
            0%,100% { transform: translateY(0px) scale(1); }
            50%      { transform: translateY(-20px) scale(1.03); }
        }
        .brand-panel::before {
            content:''; position:absolute; inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .brand-content { position:relative; z-index:2; }
        .brand-logo { display:flex; align-items:center; gap:14px; margin-bottom:44px; }
        .brand-logo-icon {
            width:52px; height:52px;
            background:rgba(255,255,255,0.15);
            border:2px solid rgba(255,255,255,0.3);
            border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem; color:white; backdrop-filter:blur(8px);
        }
        .brand-logo span { font-size:1.2rem; font-weight:700; color:white; letter-spacing:0.3px; }

        .brand-tagline { font-size:clamp(1.6rem,2.8vw,2.2rem); font-weight:800; color:white; line-height:1.2; margin-bottom:16px; letter-spacing:-0.5px; }
        .brand-tagline span { color:var(--accent); }
        .brand-sub { font-size:0.9rem; color:rgba(255,255,255,0.72); line-height:1.7; max-width:340px; margin-bottom:40px; }

        /* Steps preview */
        .steps-preview { display:flex; flex-direction:column; gap:0; }
        .step-item {
            display:flex; align-items:flex-start; gap:16px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.10);
            animation: slideIn 0.5s ease both;
        }
        .step-item:last-child { border-bottom:none; }
        .step-item:nth-child(1) { animation-delay:0.1s; }
        .step-item:nth-child(2) { animation-delay:0.2s; }
        .step-item:nth-child(3) { animation-delay:0.3s; }
        .step-item:nth-child(4) { animation-delay:0.4s; }
        @keyframes slideIn {
            from { opacity:0; transform:translateX(-16px); }
            to   { opacity:1; transform:translateX(0); }
        }
        .step-num {
            width:30px; height:30px; flex-shrink:0;
            background:rgba(255,255,255,0.15);
            border:1.5px solid rgba(255,255,255,0.25);
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:12px; font-weight:700; color:white;
        }
        .step-text strong { display:block; font-size:0.88rem; color:white; font-weight:600; }
        .step-text span   { font-size:0.78rem; color:rgba(255,255,255,0.60); }

        /* ═══════════════════════════════
           RIGHT PANEL — Form
        ═══════════════════════════════ */
        .form-panel {
            flex:1;
            display:flex;
            flex-direction:column;
            justify-content:flex-start;
            align-items:center;
            padding: 72px 40px 60px;
            background: var(--white);
            overflow-y: auto;
            position: sticky;
            top: 0;
            height: 100vh;
            flex-shrink: 0;
        }
        .form-panel::before {
            content:''; position:absolute; bottom:-100px; left:-100px;
            width:300px; height:300px;
            background:radial-gradient(circle, rgba(0,119,255,0.05) 0%, transparent 70%);
            border-radius:50%; pointer-events:none;
        }

        .form-box {
            width:100%; max-width:480px; padding-top:20px;
            animation: fadeUp 0.5s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .form-header { margin-bottom:28px; }
        .form-header h1 { font-size:1.8rem; font-weight:800; color:var(--text-dark); line-height:1.2; margin-bottom:6px; letter-spacing:-0.4px; }
        .form-header p { font-size:0.88rem; color:var(--text-light); }
        .form-header p a { color:var(--secondary); font-weight:600; text-decoration:none; }
        .form-header p a:hover { text-decoration:underline; }

        /* Progress bar */
        .progress-wrap { margin-bottom:28px; }
        .progress-steps { display:flex; justify-content:space-between; margin-bottom:8px; }
        .progress-step-label { font-size:11px; font-weight:600; color:#bbb; text-align:center; flex:1; transition:color 0.3s; }
        .progress-step-label.active { color:var(--primary); }
        .progress-step-label.done   { color:var(--success); }
        .progress-bar-track { height:4px; background:#e8edf5; border-radius:4px; overflow:hidden; }
        .progress-bar-fill  { height:100%; background:linear-gradient(90deg,var(--primary),var(--secondary)); border-radius:4px; transition:width 0.4s ease; }

        /* Step panels */
        .step-panel { display:none; }
        .step-panel.active { display:block; animation:fadeUp 0.35s ease both; }

        /* Fields */
        .field { margin-bottom:18px; }
        .field label { display:block; font-size:13px; font-weight:600; color:var(--text-mid); margin-bottom:7px; }
        .field label span.req { color:var(--secondary); margin-left:2px; }

        .input-wrap { position:relative; cursor:pointer; }
        .input-wrap i.fi { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#b0bec5; font-size:0.9rem; pointer-events:none; transition:color 0.2s; z-index:1; }
        .input-wrap input[type="date"]::-webkit-calendar-picker-indicator { position:absolute; left:0; top:0; width:100%; height:100%; opacity:0; cursor:pointer; }
        .input-wrap input, .input-wrap select {
            width:100%; padding:13px 44px 13px 44px;
            border:2px solid #e8edf5; border-radius:var(--radius);
            font-size:14px; font-family:'Poppins',sans-serif;
            background:#f8faff; color:var(--text-dark);
            transition:border-color 0.2s, background 0.2s, box-shadow 0.2s;
            outline:none; appearance:none;
        }
        .input-wrap select { padding-right:40px; }
        .input-wrap input:focus, .input-wrap select:focus {
            border-color:var(--secondary); background:var(--white);
            box-shadow:0 0 0 4px rgba(0,119,255,0.10);
        }
        .input-wrap:focus-within i.fi { color:var(--secondary); }
        .select-arr { position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#b0bec5; pointer-events:none; font-size:0.8rem; }

        .field-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

        /* Eye btn */
        .eye-btn { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#b0bec5; font-size:0.9rem; padding:4px; transition:color 0.2s; }
        .eye-btn:hover { color:var(--secondary); }

        /* Password strength */
        .pwd-strength { margin-top:8px; }
        .pwd-bars { display:flex; gap:4px; margin-bottom:5px; }
        .pwd-bar { flex:1; height:4px; border-radius:4px; background:#e0e0e0; transition:background 0.3s; }
        .pwd-hint { font-size:11px; font-weight:600; color:var(--text-light); }

        /* Error */
        .field.error .input-wrap input,
        .field.error .input-wrap select { border-color:var(--error); background:#fff5f5; }
        .field-error { font-size:12px; color:var(--error); font-weight:500; margin-top:5px; display:none; }
        .field.error .field-error { display:block; }

        /* Checkbox */
        .check-row { display:flex; align-items:flex-start; gap:10px; margin-bottom:18px; }
        .check-row input[type="checkbox"] { accent-color:var(--primary); width:16px; height:16px; margin-top:2px; flex-shrink:0; cursor:pointer; }
        .check-row label { font-size:13px; color:var(--text-mid); line-height:1.5; cursor:pointer; }
        .check-row label a { color:var(--secondary); font-weight:600; text-decoration:none; }
        .check-row label a:hover { text-decoration:underline; }
        .field.error .check-row input { outline:2px solid var(--error); }

        /* Alert */
        .alert { display:none; padding:12px 16px; border-radius:12px; font-size:13px; font-weight:500; margin-bottom:18px; align-items:center; gap:10px; }
        .alert.error   { background:#fff5f5; border:1.5px solid #ffcdd2; color:var(--error); display:flex; }
        .alert.success { background:#f1f8e9; border:1.5px solid #c8e6c9; color:var(--success); display:flex; }

        /* Buttons */
        .btn-row { display:flex; gap:12px; margin-top:6px; }
        .btn-primary {
            flex:1; padding:13px;
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            color:white; border:none; border-radius:var(--radius);
            font-size:14px; font-weight:700; font-family:'Poppins',sans-serif;
            cursor:pointer; transition:opacity 0.2s, transform 0.2s, box-shadow 0.2s;
            display:flex; align-items:center; justify-content:center; gap:9px;
            box-shadow:0 6px 20px rgba(0,74,173,0.26); letter-spacing:0.3px; position:relative; overflow:hidden;
        }
        .btn-primary::after { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(255,255,255,0.12),transparent); opacity:0; transition:opacity 0.2s; }
        .btn-primary:hover { opacity:0.92; transform:translateY(-2px); box-shadow:0 10px 28px rgba(0,74,173,0.32); }
        .btn-primary:hover::after { opacity:1; }
        .btn-primary:disabled { background:#ccc; cursor:not-allowed; box-shadow:none; transform:none; }

        .btn-outline {
            padding:13px 20px;
            border:2px solid #e0e8f5; border-radius:var(--radius);
            background:var(--white); color:var(--text-mid);
            font-size:14px; font-weight:600; font-family:'Poppins',sans-serif;
            cursor:pointer; transition:border-color 0.2s, background 0.2s;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-outline:hover { border-color:var(--primary); color:var(--primary); background:#f0f5ff; }

        /* Back to home */
        .back-home { position:absolute; top:24px; left:28px; display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text-light); text-decoration:none; transition:color 0.2s; }
        .back-home:hover { color:var(--primary); }

        /* Loading spinner */
        .spinner { display:inline-block; width:15px; height:15px; border:2px solid rgba(255,255,255,0.4); border-top-color:white; border-radius:50%; animation:spin 0.7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* Footer */
        .form-footer { margin-top:24px; text-align:center; font-size:12px; color:#aaa; line-height:1.6; }
        .form-footer a { color:var(--secondary); text-decoration:none; font-weight:500; }

        /* ── RESPONSIVE ── */
        @media (max-width:900px) {
            .brand-panel { display:none; }
            .form-panel { background:var(--bg); padding:28px 20px 60px; }
            .form-box { background:var(--white); padding:32px 24px; border-radius:20px; box-shadow:0 8px 32px rgba(0,74,173,0.10); padding-top:24px; }
            .back-home { top:16px; left:16px; }
        }
        @media (max-width:480px) {
            .field-grid { grid-template-columns:1fr; }
            .form-box { padding:24px 16px; }
        }
    </style>
</head>
<body>

<!-- ═══ LEFT BRAND PANEL ═══ -->
<div class="brand-panel">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>

    <div class="brand-content">
        <div class="brand-logo">
            <div class="brand-logo-icon"><i class="fa-solid fa-building-columns"></i></div>
            <span>E-Gov Portal</span>
        </div>

        <h2 class="brand-tagline">One account.<br><span>All services.</span></h2>
        <p class="brand-sub">Create your free E-Gov account to apply for scholarships, register as a taxpayer, enroll in TESDA programs, and track all your government applications in one dashboard.</p>

        <div class="steps-preview">
            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-text">
                    <strong>Create your account</strong>
                    <span>Basic credentials — takes under 2 minutes</span>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-text">
                    <strong>Complete your profile</strong>
                    <span>Personal details &amp; Security</span>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <div class="step-text">
                    <strong>Start applying</strong>
                    <span>Access CHED, BIR, TESDA &amp; more</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ RIGHT FORM PANEL ═══ -->

<div class="form-panel">
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="form-box">
        <div class="form-header">
            <h1>Create your account ✨</h1>
            <p>Already have one? <a href="login.php">Sign in here</a></p>
        </div>

        <!-- Progress -->
        <div class="progress-wrap">
            <div class="progress-steps">
                <span class="progress-step-label active" id="plabel-1">Account</span>
                <span class="progress-step-label" id="plabel-2">Profile</span>
                <span class="progress-step-label" id="plabel-3">Security</span>
                <span class="progress-step-label" id="plabel-4">Confirm</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill" id="progressFill" style="width:25%"></div>
            </div>
        </div>

        <!-- Alert -->
        <div class="alert" id="regAlert">
            <i class="fas fa-exclamation-circle"></i>
            <span id="regAlertMsg"></span>
        </div>

<form action="success_register.php" method="POST" id="registerForm">

        <!-- ══ STEP 1: Account ══ -->
        <div class="step-panel active" id="step-1">
            <div class="field" id="f1-email">
                <label for="reg-email">Email Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-envelope fi"></i>
                    <input type="email" id="reg-email" name="email" placeholder="you@email.com" autocomplete="email">
                </div>
                <p class="field-error" id="e1-email">Please enter a valid email address.</p>
            </div>
            <div class="field" id="f1-username">
                <label for="reg-username">Username <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-at fi"></i>
                    <input type="text" id="reg-username" name="username" placeholder="e.g. juandelacruz" autocomplete="username">
                </div>
                <p class="field-error" id="e1-username">Username must be 3–20 characters (letters, numbers, underscores).</p>
            </div>

            <div class="btn-row">
                <button type="button" class="btn-primary" onclick="nextStep(1)">
                    Continue <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- ══ STEP 2: Profile ══ -->
        <div class="step-panel" id="step-2">
            <div class="field-grid">
                <div class="field" id="f2-fname">
                    <label for="reg-fname">First Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-user fi"></i>
                        <input type="text" id="reg-fname" name="firstname" placeholder="e.g. Maria">
                    </div>
                    <p class="field-error">First name is required.</p>
                </div>
                <div class="field" id="f2-lname">
                    <label for="reg-lname">Last Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-user fi"></i>
                        <input type="text" id="reg-lname" name="lastname" placeholder="e.g. Dela Cruz">
                    </div>
                    <p class="field-error">Last name is required.</p>
                </div>
            </div>
            <div class="field" id="f2-mobile">
                <label for="reg-mobile">Mobile Number <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-mobile-alt fi"></i>
                    <input type="tel" id="reg-mobile" name="mnumber" placeholder="09XXXXXXXXX" maxlength="12">
                </div>
                <p class="field-error">Enter a valid Philippine mobile number (09XXXXXXXXX).</p>
            </div>
            <div class="field" id="f2-address">
                <label for="reg-address">Current Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-map-marker-alt fi"></i>
                    <input type="text" id="reg-address" name="address" placeholder="Street, Barangay, City/Municipality, Province">
                </div>
                <p class="field-error">Please enter your current address.</p>
            </div>
            <div class="field-grid">
                <div class="field" id="f2-dob">
                    <label for="reg-dob">Date of Birth <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-calendar-alt fi"></i>
                        <input type="date" id="reg-dob" name="birthdate">
                    </div>
                    <p class="field-error">Date of birth is required.</p>
                </div>
                <div class="field" id="f2-gender">
                    <label for="reg-gender">Gender <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-venus-mars fi"></i>
                        <select id="reg-gender" name="gender">
                            <option value="" disabled selected>Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Non-binary">Non-binary</option>
                            <option value="Prefer not to say">Prefer not to say</option>
                        </select>
                        <i class="fas fa-chevron-down select-arr"></i>
                    </div>
                    <p class="field-error">Please select your gender.</p>
                </div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn-outline" onclick="prevStep(2)">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-primary" onclick="nextStep(2)">
                    Continue <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- ══ STEP 3: Security ══ -->
        <div class="step-panel" id="step-3">
            <div class="field" id="f3-pwd">
                <label for="reg-pwd">Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-lock fi"></i>
                    <input type="password" id="reg-pwd" name="password" placeholder="At least 8 characters" oninput="evalStrength(this.value)">
                    <button type="button" class="eye-btn" onclick="toggleEye('reg-pwd','eye3')">
                        <i class="fas fa-eye" id="eye3"></i>
                    </button>
                </div>
                <div class="pwd-strength">
                    <div class="pwd-bars">
                        <div class="pwd-bar" id="b1"></div>
                        <div class="pwd-bar" id="b2"></div>
                        <div class="pwd-bar" id="b3"></div>
                        <div class="pwd-bar" id="b4"></div>
                    </div>
                    <p class="pwd-hint" id="pwdHint">Use 8+ characters with letters, numbers &amp; symbols</p>
                </div>
                <p class="field-error">Password must be at least 8 characters.</p>
            </div>
            <div class="field" id="f3-confirm">
                <label for="reg-confirm">Confirm Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-lock fi"></i>
                    <input type="password" id="reg-confirm" name="cpassword" placeholder="Re-enter your password">
                    <button type="button" class="eye-btn" onclick="toggleEye('reg-confirm','eye3b')">
                        <i class="fas fa-eye" id="eye3b"></i>
                    </button>
                </div>
                <p class="field-error" id="e3-confirm">Passwords do not match.</p>
            </div>

            <div class="btn-row">
                <button type="button" class="btn-outline" onclick="prevStep(3)">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="button" class="btn-primary" onclick="nextStep(3)">
                    Continue <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- ══ STEP 4: Confirm ══ -->
        <div class="step-panel" id="step-4">
            <!-- Summary card -->
            <div style="background:#f8faff;border:2px solid #e0e8ff;border-radius:14px;padding:18px 20px;margin-bottom:22px;">
                <p style="font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">Account Summary</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div><p style="font-size:11px;color:var(--text-light);">Name</p><p style="font-size:13px;font-weight:600;color:var(--text-dark);" id="sum-name">—</p></div>
                    <div><p style="font-size:11px;color:var(--text-light);">Email</p><p style="font-size:13px;font-weight:600;color:var(--text-dark);word-break:break-all;" id="sum-email">—</p></div>
                    <div><p style="font-size:11px;color:var(--text-light);">Mobile</p><p style="font-size:13px;font-weight:600;color:var(--text-dark);" id="sum-mobile">—</p></div>
                    <div><p style="font-size:11px;color:var(--text-light);">Date of Birth</p><p style="font-size:13px;font-weight:600;color:var(--text-dark);" id="sum-dob">—</p></div>
                    <div><p style="font-size:11px;color:var(--text-light);">Username</p><p style="font-size:13px;font-weight:600;color:var(--primary);" id="sum-username">—</p></div>
                    <div><p style="font-size:11px;color:var(--text-light);">Gender</p><p style="font-size:13px;font-weight:600;color:var(--text-dark);" id="sum-gender">—</p></div>
                    <div style="grid-column:1/-1"><p style="font-size:11px;color:var(--text-light);">Address</p><p style="font-size:13px;font-weight:600;color:var(--text-dark);" id="sum-address">—</p></div>
                </div>
            </div>

            <div class="field" id="f4-terms">
                <div class="check-row">
                    <input type="checkbox" id="terms">
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a> of the E-Gov Portal, in compliance with the Data Privacy Act of 2012 (R.A. 10173).</label>
                </div>
                <p class="field-error">You must agree to the Terms of Service to continue.</p>
            </div>
            <div class="check-row">
                <input type="checkbox" id="dataConsent">
                <label for="dataConsent">I consent to the collection, processing, and use of my personal information for government service applications.</label>
            </div>
            <div class="check-row">
                <input type="checkbox" id="newsletter">
                <label for="newsletter">Send me updates about new government services and scholarship opportunities. <span style="color:#bbb;">(Optional)</span></label>
            </div>

            <div class="btn-row" style="margin-top:10px;">
                <button type="button" class="btn-outline" onclick="prevStep(4)">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="submit" class="btn-primary" id="createBtn" onclick="createAccount()">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </div>
        </div>

</form>

        <div class="form-footer">
            By creating an account, you confirm that all information provided is accurate and truthful.
            <a href="#">Privacy Policy</a> · <a href="#">Terms of Use</a>
        </div>
    </div>
</div>

<script>
    let currentStep = 1;
    const TOTAL = 4;

    // ── Step navigation ──
    function nextStep(from) {
        if (!validateStep(from)) return;
        if (from === TOTAL - 1) fillSummary();
        goToStep(from + 1);
    }
    function prevStep(from) { goToStep(from - 1); }

    function goToStep(n) {
        document.getElementById('step-' + currentStep).classList.remove('active');
        currentStep = n;
        document.getElementById('step-' + n).classList.add('active');
        hideAlert();
        updateProgress();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateProgress() {
        const pct = (currentStep / TOTAL) * 100;
        document.getElementById('progressFill').style.width = pct + '%';
        for (let i = 1; i <= TOTAL; i++) {
            const el = document.getElementById('plabel-' + i);
            el.classList.remove('active', 'done');
            if (i < currentStep) el.classList.add('done');
            else if (i === currentStep) el.classList.add('active');
        }
    }

    // ── Validation ──
    function setErr(id, hasErr, msg) {
        const field = document.getElementById(id);
        if (!field) return;
        field.classList.toggle('error', hasErr);
        if (msg) { const e = field.querySelector('.field-error'); if (e) e.textContent = msg; }
    }
    function clearErr(id) { const f = document.getElementById(id); if (f) f.classList.remove('error'); }

    function validateEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); }
    function validateUsername(v) { return /^[a-zA-Z0-9_]{3,20}$/.test(v.trim()); }
    function validateMobile(v) { return /^09\d{9}$/.test(v.trim()); }

    function validateStep(step) {
        let ok = true;
        hideAlert();

        if (step === 1) {
            const email = document.getElementById('reg-email').value;
            const uname = document.getElementById('reg-username').value;
            if (!validateEmail(email)) { setErr('f1-email', true); ok = false; } else clearErr('f1-email');
            if (!validateUsername(uname)) { setErr('f1-username', true); ok = false; } else clearErr('f1-username');
        }
        if (step === 2) {
            const fname = document.getElementById('reg-fname').value.trim();
            const lname = document.getElementById('reg-lname').value.trim();
            const mob   = document.getElementById('reg-mobile').value.trim();
            const addr  = document.getElementById('reg-address').value.trim();
            const dob   = document.getElementById('reg-dob').value;
            const gender = document.getElementById('reg-gender').value;
            if (!fname) { setErr('f2-fname', true); ok = false; } else clearErr('f2-fname');
            if (!lname) { setErr('f2-lname', true); ok = false; } else clearErr('f2-lname');
            if (!validateMobile(mob)) { setErr('f2-mobile', true); ok = false; } else clearErr('f2-mobile');
            if (!addr) { setErr('f2-address', true); ok = false; } else clearErr('f2-address');
            if (!dob) { setErr('f2-dob', true); ok = false; } else clearErr('f2-dob');
            if (!gender) { setErr('f2-gender', true); ok = false; } else clearErr('f2-gender');
        }
        if (step === 3) {
            const pwd = document.getElementById('reg-pwd').value;
            const cfm = document.getElementById('reg-confirm').value;
            if (pwd.length < 8) { setErr('f3-pwd', true); ok = false; } else clearErr('f3-pwd');
            if (pwd !== cfm || !cfm) { setErr('f3-confirm', true, 'Passwords do not match.'); ok = false; } else clearErr('f3-confirm');
        }
        if (step === 4) {
            if (!document.getElementById('terms').checked) { setErr('f4-terms', true); ok = false; } else clearErr('f4-terms');
        }
        return ok;
    }

    // ── Password strength ──
    function evalStrength(pwd) {
        let score = 0;
        if (pwd.length >= 8)  score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;

        const colors = ['#e53935','#fb8c00','#fdd835','#43a047'];
        const labels = ['Weak','Fair','Good','Strong'];
        const bars = ['b1','b2','b3','b4'];

        bars.forEach((id, i) => {
            const el = document.getElementById(id);
            el.style.background = i < score ? colors[score - 1] : '#e0e0e0';
        });
        const hint = document.getElementById('pwdHint');
        if (pwd.length === 0) { hint.textContent = 'Use 8+ characters with letters, numbers & symbols'; hint.style.color = 'var(--text-light)'; }
        else { hint.textContent = labels[score - 1] || 'Very Weak'; hint.style.color = colors[score - 1] || '#e53935'; }
    }

    // ── Fill summary (step 4) ──
    function fillSummary() {
        const fname = document.getElementById('reg-fname').value.trim();
        const lname = document.getElementById('reg-lname').value.trim();
        document.getElementById('sum-name').textContent    = fname + ' ' + lname;
        document.getElementById('sum-email').textContent   = document.getElementById('reg-email').value.trim();
        document.getElementById('sum-mobile').textContent  = document.getElementById('reg-mobile').value.trim();
        document.getElementById('sum-dob').textContent     = document.getElementById('reg-dob').value;
        document.getElementById('sum-username').textContent= '@' + document.getElementById('reg-username').value.trim();
        document.getElementById('sum-gender').textContent  = document.getElementById('reg-gender').value || '—';
        const sumAddr = document.getElementById('sum-address');
        if (sumAddr) sumAddr.textContent = document.getElementById('reg-address').value.trim() || '—';
    };

    // ── Helpers ──
    function toggleEye(inputId, btnId) {
        const input = document.getElementById(inputId);
        const icon  = document.querySelector('#' + btnId);
        if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
        else { input.type = 'password'; icon.className = 'fas fa-eye'; }
    }
    function showAlert(type, msg) {
        const a = document.getElementById('regAlert');
        a.className = 'alert ' + type;
        document.getElementById('regAlertMsg').textContent = msg;
        a.style.display = 'flex';
    }
    function hideAlert() { document.getElementById('regAlert').style.display = 'none'; }

    // ── Create Account — final validation before form submits ──
    function createAccount() {
        if (!validateStep(4)) {
            event.preventDefault();
            return false;
        }
        // All good — let the form submit naturally
    }

    // Clear errors on input
    ['reg-email','reg-username','reg-fname','reg-lname','reg-mobile','reg-address','reg-dob','reg-pwd','reg-confirm'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => {
            const fieldId = 'f' + currentStep + '-' + id.replace('reg-','');
            clearErr(fieldId);
        });
    });
</script>

</body>
</html>