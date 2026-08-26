<?php
session_start();
include('connection.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration — E-Gov Portal</title>
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
        }

        /* ═══ LEFT BRAND PANEL ═══ */
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
        .shape { position:absolute; border-radius:50%; opacity:0.10; animation:drift 9s ease-in-out infinite; }
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

        /* Next steps list */
        .next-steps { display:flex; flex-direction:column; gap:0; }
        .ns-item {
            display:flex; align-items:flex-start; gap:16px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.10);
            animation: slideIn 0.5s ease both;
        }
        .ns-item:last-child { border-bottom:none; }
        .ns-item:nth-child(1) { animation-delay:0.1s; }
        .ns-item:nth-child(2) { animation-delay:0.25s; }
        .ns-item:nth-child(3) { animation-delay:0.4s; }
        @keyframes slideIn {
            from { opacity:0; transform:translateX(-16px); }
            to   { opacity:1; transform:translateX(0); }
        }
        .ns-icon {
            width:34px; height:34px; flex-shrink:0;
            background:rgba(255,255,255,0.15);
            border:1.5px solid rgba(255,255,255,0.25);
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:13px; color:var(--accent);
        }
        .ns-text strong { display:block; font-size:0.88rem; color:white; font-weight:600; }
        .ns-text span   { font-size:0.78rem; color:rgba(255,255,255,0.60); }

        /* ═══ RIGHT RESULT PANEL ═══ */
        .result-panel {
            flex:1;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            padding: 60px 40px;
            background: var(--white);
            position: relative;
            overflow: hidden;
        }
        .result-panel::before {
            content:''; position:absolute; bottom:-100px; left:-100px;
            width:300px; height:300px;
            background:radial-gradient(circle, rgba(0,119,255,0.05) 0%, transparent 70%);
            border-radius:50%; pointer-events:none;
        }

        .result-box {
            width:100%; max-width:460px;
            animation: fadeUp 0.6s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ── SUCCESS STATE ── */
        .success-icon-wrap {
            display:flex; justify-content:center; margin-bottom:28px;
        }
        .success-circle {
            width:90px; height:90px;
            background:linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:2.4rem; color:#2e7d32;
            box-shadow: 0 8px 28px rgba(46,125,50,0.18);
            animation: popIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
            animation-delay: 0.2s;
        }
        @keyframes popIn {
            from { transform:scale(0.4); opacity:0; }
            to   { transform:scale(1); opacity:1; }
        }

        .result-box h1 {
            font-size:1.75rem; font-weight:800; color:var(--text-dark);
            letter-spacing:-0.4px; margin-bottom:10px; text-align:center;
        }
        .result-box .sub {
            font-size:0.9rem; color:var(--text-light); text-align:center;
            line-height:1.7; margin-bottom:28px;
        }

        /* Status badge */
        .status-badge {
            display:inline-flex; align-items:center; gap:8px;
            padding:7px 16px; border-radius:50px; font-size:12px; font-weight:700;
            margin:0 auto 28px; letter-spacing:0.3px;
        }
        .status-badge.success { background:#e8f5e9; color:#2e7d32; border:1.5px solid #c8e6c9; }
        .status-badge.error   { background:#fff5f5; color:var(--error); border:1.5px solid #ffcdd2; }
        .status-badge-wrap { display:flex; justify-content:center; }

        /* Info card */
        .info-card {
            background:#f8faff;
            border:2px solid #e0e8ff;
            border-radius:14px;
            padding:18px 20px;
            margin-bottom:28px;
        }
        .info-card-title {
            font-size:11px; font-weight:700; color:var(--text-light);
            text-transform:uppercase; letter-spacing:0.8px; margin-bottom:14px;
        }
        .info-row {
            display:flex; align-items:center; gap:10px;
            padding: 8px 0;
            border-bottom:1px solid #edf1fb;
        }
        .info-row:last-child { border-bottom:none; padding-bottom:0; }
        .info-row i { width:18px; text-align:center; color:var(--secondary); font-size:13px; }
        .info-row span { font-size:13px; color:var(--text-mid); }
        .info-row strong { font-size:13px; color:var(--text-dark); font-weight:600; }

        /* Error details */
        .error-detail {
            background:#fff5f5; border:1.5px solid #ffcdd2;
            border-radius:12px; padding:14px 16px; margin-bottom:24px;
            font-size:13px; color:var(--error); line-height:1.6;
        }

        /* Buttons */
        .btn-primary {
            width:100%; padding:14px;
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            color:white; border:none; border-radius:var(--radius);
            font-size:14px; font-weight:700; font-family:'Poppins',sans-serif;
            cursor:pointer; transition:opacity 0.2s, transform 0.2s, box-shadow 0.2s;
            display:flex; align-items:center; justify-content:center; gap:9px;
            box-shadow:0 6px 20px rgba(0,74,173,0.26); letter-spacing:0.3px;
            text-decoration:none; margin-bottom:12px;
        }
        .btn-primary:hover { opacity:0.92; transform:translateY(-2px); box-shadow:0 10px 28px rgba(0,74,173,0.32); }

        .btn-outline {
            width:100%; padding:13px;
            border:2px solid #e0e8f5; border-radius:var(--radius);
            background:var(--white); color:var(--text-mid);
            font-size:14px; font-weight:600; font-family:'Poppins',sans-serif;
            cursor:pointer; transition:border-color 0.2s, background 0.2s;
            display:flex; align-items:center; justify-content:center; gap:8px;
            text-decoration:none;
        }
        .btn-outline:hover { border-color:var(--primary); color:var(--primary); background:#f0f5ff; }

        /* Divider */
        .divider { display:flex; align-items:center; gap:12px; margin:16px 0; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:#e8edf5; }
        .divider span { font-size:12px; color:#bbb; font-weight:500; }

        /* Footer note */
        .result-footer { margin-top:20px; text-align:center; font-size:12px; color:#aaa; line-height:1.6; }
        .result-footer a { color:var(--secondary); text-decoration:none; font-weight:500; }

        /* ── RESPONSIVE ── */
        @media (max-width:900px) {
            .brand-panel { display:none; }
            .result-panel { background:var(--bg); padding:40px 20px; }
            .result-box { background:var(--white); padding:32px 24px; border-radius:20px; box-shadow:0 8px 32px rgba(0,74,173,0.10); }
        }
        @media (max-width:480px) {
            .result-box { padding:24px 16px; }
            .result-box h1 { font-size:1.4rem; }
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

        <h2 class="brand-tagline">You're almost<br><span>ready to go.</span></h2>
        <p class="brand-sub">Your account gives you access to CHED, BIR, TESDA, and dozens of other government services — all in one place.</p>

        <div class="next-steps">
            <div class="ns-item">
                <div class="ns-icon"><i class="fas fa-sign-in-alt"></i></div>
                <div class="ns-text">
                    <strong>Sign in to your account</strong>
                    <span>Use your email and password to log in</span>
                </div>
            </div>
            <div class="ns-item">
                <div class="ns-icon"><i class="fas fa-id-card"></i></div>
                <div class="ns-text">
                    <strong>Complete your profile</strong>
                    <span>Add supporting documents when needed</span>
                </div>
            </div>
            <div class="ns-item">
                <div class="ns-icon"><i class="fas fa-list-check"></i></div>
                <div class="ns-text">
                    <strong>Browse available services</strong>
                    <span>Apply for scholarships, clearances &amp; more</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ RIGHT RESULT PANEL ═══ -->
<div class="result-panel">
    <div class="result-box">

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ── Check for duplicate email or username ──
    $checkStmt = $Connection->prepare(
        "SELECT id_application FROM application WHERE email = ? OR username = ? LIMIT 1"
    );
    $checkStmt->bind_param("ss", $_POST['email'], $_POST['username']);
    $checkStmt->execute();
    $checkStmt->store_result();
    $isDuplicate = ($checkStmt->num_rows > 0);

    if ($isDuplicate) {
        // Find out which field is taken for a helpful message
        $checkStmt->close();
        $emailCheck = $Connection->prepare(
            "SELECT id_application FROM application WHERE email = ? LIMIT 1"
        );
        $emailCheck->bind_param("s", $_POST['email']);
        $emailCheck->execute();
        $emailCheck->store_result();
        $emailTaken = ($emailCheck->num_rows > 0);
        $emailCheck->close();
        $dupMsg = $emailTaken
            ? "An account with that email address already exists."
            : "That username is already taken. Please choose a different one.";
?>
        <div class="success-icon-wrap">
            <div class="success-circle" style="background:linear-gradient(135deg,#fff5f5,#ffcdd2);color:var(--error);box-shadow:0 8px 28px rgba(229,57,53,0.18);">
                <i class="fas fa-xmark"></i>
            </div>
        </div>
        <h1>Account Already Exists</h1>
        <p class="sub"><?= htmlspecialchars($dupMsg) ?></p>
        <div class="status-badge-wrap">
            <div class="status-badge error">
                <i class="fas fa-circle-exclamation"></i> Duplicate Account
            </div>
        </div>
        <a href="register.php" class="btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Registration
        </a>
        <div class="divider"><span>or</span></div>
        <a href="login.php" class="btn-outline">
            <i class="fas fa-sign-in-alt"></i> Sign In Instead
        </a>
        <div class="result-footer">
            <a href="index.php">Back to Home</a> &middot; <a href="#">Privacy Policy</a>
        </div>
<?php
    } else {
        // No duplicate — proceed with insert
        $checkStmt->close();

        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $Connection->prepare("
            INSERT INTO application
                (id_application, email, username, firstname, lastname,
                 mnumber, address, birthdate, gender, password, cpassword)
            VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssssssss",
            $_POST['email'],
            $_POST['username'],
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['mnumber'],
            $_POST['address'],
            $_POST['birthdate'],
            $_POST['gender'],
            $hashedPassword,
            $hashedPassword
        );
        $result = $stmt->execute();

        if ($result) {
            // ── SUCCESS ──
            // Set session so Browse Services shows profile name immediately
            $newId = $Connection->insert_id;
            $_SESSION['validpage']      = TRUE;
            $_SESSION['id_application'] = $newId;
            $_SESSION['firstname']      = $_POST['firstname'];
            $_SESSION['lastname']       = $_POST['lastname'];
            $_SESSION['email']          = $_POST['email'];
            $_SESSION['username']       = $_POST['username'];

            $fname = htmlspecialchars($_POST['firstname']);
            $email = htmlspecialchars($_POST['email']);
            $uname = htmlspecialchars($_POST['username']);
?>
        <div class="success-icon-wrap">
            <div class="success-circle"><i class="fas fa-check"></i></div>
        </div>
        <h1>Welcome, <?= $fname ?>! 🎉</h1>
        <p class="sub">Your E-Gov Portal account has been created successfully. You can now sign in and access government services.</p>
        <div class="status-badge-wrap">
            <div class="status-badge success">
                <i class="fas fa-circle-check"></i> Registration Successful
            </div>
        </div>
        <div class="info-card">
            <p class="info-card-title">Account Details</p>
            <div class="info-row">
                <i class="fas fa-envelope"></i>
                <span>Email &nbsp;&middot;&nbsp;</span>
                <strong><?= $email ?></strong>
            </div>
            <div class="info-row">
                <i class="fas fa-at"></i>
                <span>Username &nbsp;&middot;&nbsp;</span>
                <strong>@<?= $uname ?></strong>
            </div>
            <div class="info-row">
                <i class="fas fa-shield-halved"></i>
                <span>Status &nbsp;&middot;&nbsp;</span>
                <strong style="color:#2e7d32;">Active</strong>
            </div>
        </div>
        <a href="login.php" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i> Sign In to Your Account
        </a>
        <div class="divider"><span>or</span></div>
        <a href="service.php" class="btn-outline">
            <i class="fas fa-list-check"></i> Browse Services
        </a>
        <div class="result-footer">
            Keep your credentials safe. Never share your password.<br>
            <a href="index.php">Back to Home</a> &middot; <a href="#">Privacy Policy</a>
        </div>
<?php
        } else {
            // ── DB ERROR ──
?>
        <div class="success-icon-wrap">
            <div class="success-circle" style="background:linear-gradient(135deg,#fff5f5,#ffcdd2);color:var(--error);box-shadow:0 8px 28px rgba(229,57,53,0.18);">
                <i class="fas fa-xmark"></i>
            </div>
        </div>
        <h1>Registration Failed</h1>
        <p class="sub">We encountered an issue while saving your account. Please try again or contact support if the problem persists.</p>
        <div class="status-badge-wrap">
            <div class="status-badge error">
                <i class="fas fa-circle-exclamation"></i> Error Encountered
            </div>
        </div>
        <div class="error-detail">
            <i class="fas fa-triangle-exclamation" style="margin-right:6px;"></i>
            <?= htmlspecialchars($stmt->error ?: mysqli_error($Connection)) ?>
        </div>
        <a href="register.php" class="btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Registration
        </a>
        <div class="divider"><span>or</span></div>
        <a href="contact.php" class="btn-outline">
            <i class="fas fa-envelope"></i> Contact Support
        </a>
        <div class="result-footer">
            <a href="index.php">Back to Home</a> &middot; <a href="#">Privacy Policy</a>
        </div>
<?php
        } // end if ($result)
    } // end else (no duplicate)

} else {
    // ── INVALID ACCESS — direct URL visit without POST ──
?>
        <div class="success-icon-wrap">
            <div class="success-circle" style="background:linear-gradient(135deg,#fff8e1,#ffecb3);color:#f57f17;box-shadow:0 8px 28px rgba(245,127,23,0.18);">
                <i class="fas fa-ban"></i>
            </div>
        </div>
        <h1>Invalid Access</h1>
        <p class="sub">This page can only be reached by completing the registration form. Please go back and fill out the sign-up form.</p>
        <div class="status-badge-wrap">
            <div class="status-badge error">
                <i class="fas fa-circle-exclamation"></i> Direct Access Not Allowed
            </div>
        </div>
        <a href="register.php" class="btn-primary">
            <i class="fas fa-user-plus"></i> Go to Registration
        </a>
        <div class="divider"><span>or</span></div>
        <a href="login.php" class="btn-outline">
            <i class="fas fa-sign-in-alt"></i> Sign In Instead
        </a>
        <div class="result-footer">
            <a href="index.php">Back to Home</a> &middot; <a href="#">Privacy Policy</a>
        </div>
<?php } ?>

    </div><!-- /.result-box -->
</div><!-- /.result-panel -->

</body>
</html>