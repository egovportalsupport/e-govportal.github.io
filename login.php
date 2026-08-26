<?php
include('connection.php');

if(isset($_GET['logout'])){
	    session_start();
		session_unset();
		session_destroy();
		$success=1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — E-Gov Portal</title>
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
            --shadow-lg: 0 20px 60px rgba(0,74,173,0.18);
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ═══════════════════════════════
           LEFT PANEL — Brand
        ═══════════════════════════════ */
        .brand-panel {
            width: 44%;
            background: linear-gradient(145deg, #003a8c 0%, #0055cc 45%, #0077ff 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 56px;
            overflow: hidden;
        }

        /* Animated geometric shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.12;
            animation: drift 8s ease-in-out infinite;
        }
        .shape-1 { width:420px; height:420px; background:white; top:-120px; right:-140px; animation-delay:0s; }
        .shape-2 { width:260px; height:260px; background:var(--accent); bottom:-80px; left:-60px; animation-delay:-3s; }
        .shape-3 { width:160px; height:160px; background:white; bottom:120px; right:40px; animation-delay:-5s; opacity:0.08; }
        .shape-4 { width:80px; height:80px; background:var(--accent); top:200px; left:30px; animation-delay:-1.5s; opacity:0.2; border-radius:12px; transform:rotate(20deg); }

        @keyframes drift {
            0%,100% { transform: translateY(0px) scale(1); }
            50%      { transform: translateY(-24px) scale(1.04); }
        }

        /* Grid lines overlay */
        .brand-panel::before {
            content:'';
            position:absolute; inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .brand-content { position:relative; z-index:2; }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 52px;
        }
        .brand-logo-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: white;
            backdrop-filter: blur(8px);
        }
        .brand-logo span {
            font-size: 1.2rem; font-weight: 700; color: white;
            letter-spacing: 0.3px;
        }

        .brand-tagline {
            font-size: clamp(1.7rem, 3vw, 2.4rem);
            font-weight: 800;
            color: white;
            line-height: 1.2;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }
        .brand-tagline span { color: var(--accent); }

        .brand-sub {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.75);
            line-height: 1.7;
            max-width: 360px;
            margin-bottom: 48px;
        }

        .brand-services {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .service-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 12px;
            padding: 12px 18px;
            backdrop-filter: blur(6px);
            transition: background 0.25s;
            animation: slideIn 0.6s ease both;
        }
        .service-chip:nth-child(1) { animation-delay: 0.1s; }
        .service-chip:nth-child(2) { animation-delay: 0.2s; }
        .service-chip:nth-child(3) { animation-delay: 0.3s; }
        @keyframes slideIn {
            from { opacity:0; transform:translateX(-20px); }
            to   { opacity:1; transform:translateX(0); }
        }
        .service-chip:hover { background: rgba(255,255,255,0.18); }
        .service-chip i { font-size:1.1rem; color:var(--accent); width:22px; text-align:center; }
        .service-chip span { font-size:0.88rem; font-weight:500; color:rgba(255,255,255,0.9); }

        /* ═══════════════════════════════
           RIGHT PANEL — Form
        ═══════════════════════════════ */
        .form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px 40px;
            background: var(--white);
            position: relative;
            overflow-y: hidden;
            overflow-x: hidden;
        }

        .form-panel::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(0,119,255,0.06) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .form-box {
            width: 100%;
            max-width: 420px;
            animation: fadeUp 0.5s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .form-header { margin-bottom: 36px; }
        .form-header h1 {
            font-size: 1.9rem; font-weight: 800;
            color: var(--text-dark); line-height: 1.2;
            margin-bottom: 8px; letter-spacing: -0.4px;
        }
        .form-header p { font-size: 0.9rem; color: var(--text-light); }
        .form-header p a { color: var(--secondary); font-weight: 600; text-decoration:none; }
        .form-header p a:hover { text-decoration: underline; }

        /* Form fields */
        .field { margin-bottom: 20px; }
        .field label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: var(--text-mid);
            margin-bottom: 8px;
        }
        .field label span.req { color: var(--secondary); margin-left: 2px; }

        .input-wrap { position: relative; }
        .input-wrap i.fi {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%);
            color: #b0bec5; font-size: 0.95rem;
            transition: color 0.2s;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            padding: 13px 44px;
            border: 2px solid #e8edf5;
            border-radius: var(--radius);
            font-size: 14px; font-family: 'Poppins', sans-serif;
            background: #f8faff;
            color: var(--text-dark);
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .input-wrap input:focus {
            border-color: var(--secondary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(0,119,255,0.10);
        }
        .input-wrap input:focus ~ i.fi,
        .input-wrap:focus-within i.fi { color: var(--secondary); }

        /* Eye toggle */
        .eye-btn {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #b0bec5; font-size: 0.95rem;
            padding: 4px; line-height: 1;
            transition: color 0.2s;
        }
        .eye-btn:hover { color: var(--secondary); }

        /* Error state */
        .field.error .input-wrap input { border-color: var(--error); background: #fff5f5; }
        .field-error {
            font-size: 12px; color: var(--error); font-weight: 500;
            margin-top: 5px; display: none;
        }
        .field.error .field-error { display: block; }

        /* Options row */
        .options-row {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px;
        }
        .remember-row { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .remember-row input[type="checkbox"] { accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer; }
        .remember-row span { font-size: 13px; color: var(--text-mid); font-weight: 500; }
        .forgot-link { font-size: 13px; color: var(--secondary); font-weight: 600; text-decoration: none; }
        .forgot-link:hover { text-decoration: underline; }

        /* Submit button */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none; border-radius: var(--radius);
            font-size: 15px; font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 6px 20px rgba(0,74,173,0.28);
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }
        .btn-primary::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
            opacity: 0; transition: opacity 0.2s;
        }
        .btn-primary:hover { opacity: 0.92; transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,74,173,0.34); }
        .btn-primary:hover::after { opacity: 1; }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { background: #ccc; cursor: not-allowed; box-shadow: none; transform: none; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 14px;
            margin: 26px 0;
        }
        .divider span { font-size: 12px; color: #bbb; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; white-space: nowrap; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:#e8edf5; }

        /* Alert banner */
        .alert {
            display: none;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px; font-weight: 500;
            margin-bottom: 20px;
            align-items: center; gap: 10px;
        }
        .alert.error   { background: #fff5f5; border: 1.5px solid #ffcdd2; color: var(--error); display: flex; }
        .alert.success { background: #f1f8e9; border: 1.5px solid #c8e6c9; color: var(--success); display: flex; }

        /* Back to home */
        .back-home {
            position: absolute; top: 24px; left: 28px;
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 600; color: var(--text-light);
            text-decoration: none; transition: color 0.2s;
        }
        .back-home:hover { color: var(--primary); }

        /* Footer note */
        .form-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
            line-height: 1.6;
        }
        .form-footer a { color: var(--secondary); text-decoration: none; font-weight: 500; }
        .form-footer a:hover { text-decoration: underline; }

        /* Loading spinner in button */
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .brand-panel { display: none; }
            .form-panel { padding: 40px 28px; background: var(--bg); }
            .form-box { background: var(--white); padding: 36px 28px; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,74,173,0.10); }
            .back-home { top: 16px; left: 16px; }
        }
        @media (max-width: 480px) {
            .form-panel { padding: 24px 16px; }
            .form-box { padding: 28px 20px; }
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

    <div class="brand-content">
        <div class="brand-logo">
            <div class="brand-logo-icon"><i class="fa-solid fa-building-columns"></i></div>
            <span>E-Gov Portal</span>
        </div>

        <h2 class="brand-tagline">Your gateway to<br><span>government services</span></h2>
        <p class="brand-sub">Access all Philippine government applications in one place — CHED scholarships, BIR registration, TESDA enrollment, and more.</p>

        <div class="brand-services">
            <div class="service-chip">
                <i class="fas fa-graduation-cap"></i>
                <span>CHED Scholarship Applications</span>
            </div>
            <div class="service-chip">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>BIR Taxpayer Registration</span>
            </div>
            <div class="service-chip">
                <i class="fas fa-tools"></i>
                <span>TESDA Skills Enrollment</span>
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
            <h1>Welcome back 👋</h1>
            <p>Don't have an account? <a href="register.php">Create one free</a></p>
        </div>

        <!-- Alert -->
        <div class="alert" id="loginAlert">
            <i class="fas fa-exclamation-circle"></i>
            <span id="loginAlertMsg">Invalid email or password. Please try again.</span>
        </div>

        <form id="loginForm" novalidate action="success_login.php" method="post">
            <?php 
			if(isset($_GET['error'])) {
					echo '<div class="alert error">
					  <i class="fas fa-exclamation-circle"></i>
					  <span>Invalid email or password. Please try again.</span>
					</div>';
				}
		   if(isset($success)) {
					echo '<div class="alert success">
					  <i class="fas fa-check-circle"></i>
					  <span>You have been logged out successfully.</span>
					</div>';
				}
			?>
            <!-- Email -->
            <div class="field" id="field-email">
                <label for="email">Email Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-envelope fi"></i>
                    <input type="email" id="email" name="email" placeholder="you@email.com" autocomplete="email" required>
                </div>
                <p class="field-error">Please enter a valid email address.</p>
            </div>

            <!-- Password -->
            <div class="field" id="field-password">
                <label for="password">Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-lock fi"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    <button type="button" class="eye-btn" id="eyeBtn" onclick="toggleEye('password','eyeBtn')">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <p class="field-error">Password is required.</p>
            </div>

            <!-- Options -->
            <div class="options-row">
                <label class="remember-row">
                    <input type="checkbox" id="rememberMe" name="rememberMe">
                    <span>Remember me</span>
                </label>
                <a href="forget_password.php" class="forgot-link">Forgot password?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-primary">
                <i class="fas fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="form-footer">
            By signing in, you agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
            of the E-Gov Portal, in compliance with the Data Privacy Act of 2012 (R.A. 10173).
        </div>
    </div>
</div>

<script>
    // ── Toggle password visibility ──
    function toggleEye(inputId, btnId) {
        const input = document.getElementById(inputId);
        const icon  = document.querySelector('#' + btnId + ' i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    // ── Alert helper ──
    function showAlert(type, msg) {
        const alert = document.getElementById('loginAlert');
        const alertMsg = document.getElementById('loginAlertMsg');
        alert.className = 'alert ' + (type === 'success' ? 'success' : 'error');
        alertMsg.textContent = msg;
        alert.style.display = 'flex';
        alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function hideAlert() { document.getElementById('loginAlert').style.display = 'none'; }

    // ── Field validation ──
    function validateEmail(val) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim()); }

    function setFieldError(id, hasError) {
        document.getElementById('field-' + id).classList.toggle('error', hasError);
    }

    // ── Form submit ──
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        hideAlert();

        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        let valid = true;

        if (!validateEmail(email)) { setFieldError('email', true); valid = false; }
        else { setFieldError('email', false); }

        if (!password) { setFieldError('password', true); valid = false; }
        else { setFieldError('password', false); }

        if (!valid) {
            e.preventDefault(); // only block submit when there are errors
        }
        // if valid, form submits normally to success_login.php
    });

    // ── Clear errors on input ──
    ['email','password'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => setFieldError(id, false));
    });
</script>

</body>
</html>