<?php
session_start();
include('connection.php');

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ═══════════════════════════════════════
   AJAX ACTIONS — Forgot Password backend
═══════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    // ── Step 1: send a one-time code to the given email ──
    if ($action === 'send_code') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }

        $stmt = mysqli_prepare($Connection, "SELECT id_application, firstname, lastname FROM application WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        $fullname = $email; // fallback if names aren't bound below
        if ($exists) {
            mysqli_stmt_bind_result($stmt, $id_application, $firstname, $lastname);
            mysqli_stmt_fetch($stmt);
            $fullname = trim("$firstname $lastname") ?: $email;
        }
        mysqli_stmt_close($stmt);

        // Don't reveal whether the email is registered — respond the same either way
        if (!$exists) {
            echo json_encode(['success' => true, 'message' => 'If that email is registered, a verification code has been sent.']);
            exit;
        }

        $otp = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
        $_SESSION['reset_email']      = $email;
        $_SESSION['reset_otp']        = password_hash($otp, PASSWORD_DEFAULT);
        $_SESSION['reset_otp_expiry'] = time() + 600; // 10 minutes
        $_SESSION['reset_verified']   = false;
        $_SESSION['reset_attempts']   = 0;

        // Send via PHPMailer/SMTP (same working setup as vid_verify.php) —
        // PHP's native mail() has no configured MTA on most local/XAMPP
        // setups and fails silently, which is why the code never arrived.
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'egovportal.support@gmail.com';
            $mail->Password   = 'gjun hkfv vdrg cbcn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('egovportal.support@gmail.com', 'E-Gov Portal');
            $mail->addAddress($email, $fullname);
            $mail->isHTML(true);
            $mail->Subject = "Your E-Gov Portal Password Reset Code";

            $mail->Body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f0f4f8;font-family:Arial,Helvetica,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f4f8;padding:24px 16px;'>
<tr><td align='center'>
<table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #d0d7de;'>

  <tr><td style='background:#004aad;padding:32px 36px 24px;text-align:center;'>
    <p style='color:#ffd700;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin:0 0 6px;'>E-Gov Portal</p>
    <h1 style='color:#ffffff;font-size:22px;font-weight:700;margin:0;'>Password Reset</h1>
  </td></tr>

  <tr><td style='padding:28px 36px;'>
    <p style='font-size:15px;color:#222;line-height:1.6;margin:0 0 10px;'>Dear <strong>$fullname</strong>,</p>
    <p style='font-size:15px;color:#222;line-height:1.6;margin:0 0 20px;'>We received a request to reset your E-Gov Portal password. Use the One-Time Password below to continue.</p>

    <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f7f6;border:1px dashed #a0b0bf;border-radius:6px;margin-bottom:24px;'>
      <tr><td style='padding:24px;text-align:center;'>
        <p style='font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1.2px;margin:0 0 10px;'>Your OTP Code</p>
        <p style='font-size:36px;font-weight:700;color:#004aad;letter-spacing:10px;margin:0 0 10px;'>$otp</p>
        <p style='font-size:12px;color:#c62828;font-weight:600;margin:0;'>Expires in 10 minutes &mdash; do not share this code</p>
      </td></tr>
    </table>

    <table width='100%' cellpadding='0' cellspacing='0' style='background:#fff8e1;border-left:4px solid #ffd700;border-radius:4px;margin-bottom:8px;'>
      <tr><td style='padding:14px 18px;font-size:13px;color:#5d4037;line-height:1.6;'>
        If you did not request a password reset, you can safely ignore this email. E-Gov Portal staff will never ask for your OTP.
      </td></tr>
    </table>

  </td></tr>

  <tr><td style='background:#f4f7f6;border-top:1px solid #e0e0e0;padding:20px 36px;text-align:center;'>
    <p style='font-size:12px;color:#999;margin:4px 0;'><strong style='color:#555;'>E-Gov Portal</strong></p>
    <p style='font-size:11px;color:#aaa;margin:4px 0;'>This is an automated email. Please do not reply directly.</p>
    <p style='font-size:11px;color:#bbb;margin:4px 0;'>&copy; " . date('Y') . " Republic of the Philippines &nbsp;&middot;&nbsp; E-Gov Portal</p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
";
            $mail->AltBody = "Hello $fullname,\n\nYour password reset OTP code is: $otp\n(Expires in 10 minutes — do not share this with anyone)\n\nIf you did not request this, you can ignore this email.\n\n---\nE-Gov Portal";

            $mail->send();
        } catch (Exception $e) {
            error_log('forget_password.php send_code: PHPMailer error - ' . $mail->ErrorInfo);
            // Still respond generically so we don't reveal account existence,
            // but the real failure is now logged instead of silently eaten.
        }

        echo json_encode(['success' => true, 'message' => 'If that email is registered, a verification code has been sent.']);
        exit;
    }

    // ── Step 2: verify the entered code ──
    if ($action === 'verify_otp') {
        $otp = trim($_POST['otp'] ?? '');

        if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_otp'])) {
            echo json_encode(['success' => false, 'message' => 'Your session has expired. Please request a new code.']);
            exit;
        }
        if (time() > ($_SESSION['reset_otp_expiry'] ?? 0)) {
            unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expiry']);
            echo json_encode(['success' => false, 'message' => 'This code has expired. Please request a new one.']);
            exit;
        }

        $_SESSION['reset_attempts'] = ($_SESSION['reset_attempts'] ?? 0) + 1;
        if ($_SESSION['reset_attempts'] > 5) {
            unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_otp_expiry']);
            echo json_encode(['success' => false, 'message' => 'Too many incorrect attempts. Please request a new code.']);
            exit;
        }

        if (!password_verify($otp, $_SESSION['reset_otp'])) {
            echo json_encode(['success' => false, 'message' => 'Incorrect code. Please try again.']);
            exit;
        }

        $_SESSION['reset_verified'] = true;
        echo json_encode(['success' => true, 'message' => 'Code verified.']);
        exit;
    }

    // ── Step 3: set the new password ──
    if ($action === 'reset_password') {
        if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_verified'])) {
            echo json_encode(['success' => false, 'message' => 'Please verify your email before resetting your password.']);
            exit;
        }

        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $email  = $_SESSION['reset_email'];

        $stmt = mysqli_prepare($Connection, "UPDATE application SET password = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $hashed, $email);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Clear the reset session regardless of outcome
        unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_otp_expiry'], $_SESSION['reset_verified'], $_SESSION['reset_attempts']);

        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Something went wrong updating your password. Please try again.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — E-Gov Portal</title>
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

        /* Icon circle */
        .icon-circle {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, rgba(0,74,173,0.10), rgba(0,119,255,0.15));
            border: 2px solid rgba(0,119,255,0.20);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: var(--secondary);
            margin-bottom: 24px;
        }

        .form-header { margin-bottom: 32px; }
        .form-header h1 {
            font-size: 1.9rem; font-weight: 800;
            color: var(--text-dark); line-height: 1.2;
            margin-bottom: 10px; letter-spacing: -0.4px;
        }
        .form-header p { font-size: 0.9rem; color: var(--text-light); line-height: 1.6; }
        .form-header p a { color: var(--secondary); font-weight: 600; text-decoration:none; }
        .form-header p a:hover { text-decoration: underline; }

        /* Steps indicator */
        .steps {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 32px;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            flex: 1;
        }
        .step-circle {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            border: 2px solid #e8edf5;
            background: var(--white);
            color: #bbb;
            transition: all 0.3s;
            position: relative;
            z-index: 1;
        }
        .step.active .step-circle {
            background: var(--secondary);
            border-color: var(--secondary);
            color: white;
            box-shadow: 0 0 0 4px rgba(0,119,255,0.15);
        }
        .step.done .step-circle {
            background: #e8f5e9;
            border-color: var(--success);
            color: var(--success);
        }
        .step-label {
            font-size: 10px; font-weight: 600;
            color: #bbb;
            text-transform: uppercase; letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .step.active .step-label { color: var(--secondary); }
        .step.done .step-label { color: var(--success); }
        .step-line {
            flex: 1;
            height: 2px;
            background: #e8edf5;
            margin-bottom: 22px;
            transition: background 0.3s;
        }
        .step-line.done { background: var(--success); }

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

        /* OTP input group */
        .otp-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .otp-group input {
            width: 52px; height: 56px;
            text-align: center;
            font-size: 1.4rem; font-weight: 700;
            border: 2px solid #e8edf5;
            border-radius: 12px;
            background: #f8faff;
            color: var(--text-dark);
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .otp-group input:focus {
            border-color: var(--secondary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(0,119,255,0.10);
        }
        .otp-group input.filled {
            border-color: var(--secondary);
            background: rgba(0,119,255,0.05);
        }

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

        /* Secondary/ghost button */
        .btn-ghost {
            width: 100%;
            padding: 13px;
            background: transparent;
            color: var(--text-mid);
            border: 2px solid #e8edf5;
            border-radius: var(--radius);
            font-size: 14px; font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: 12px;
            text-decoration: none;
        }
        .btn-ghost:hover { border-color: var(--secondary); color: var(--secondary); background: rgba(0,119,255,0.04); }

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

        /* Resend row */
        .resend-row {
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            color: var(--text-light);
        }
        .resend-row button {
            background: none; border: none;
            color: var(--secondary); font-weight: 600;
            font-family: 'Poppins', sans-serif;
            font-size: 13px; cursor: pointer;
            padding: 0;
        }
        .resend-row button:disabled { color: #bbb; cursor: not-allowed; }

        /* Password strength */
        .strength-bar {
            margin-top: 8px;
            height: 4px;
            border-radius: 4px;
            background: #e8edf5;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }
        .strength-label {
            font-size: 11px; font-weight: 600;
            margin-top: 5px;
            color: #bbb;
        }

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

        /* Spinner */
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Success screen */
        .success-screen {
            text-align: center;
            display: none;
            animation: fadeUp 0.5s ease both;
        }
        .success-icon {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: var(--success);
            margin: 0 auto 24px;
            border: 2px solid #a5d6a7;
        }
        .success-screen h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 12px; }
        .success-screen p { font-size: 0.9rem; color: var(--text-light); line-height: 1.7; margin-bottom: 28px; }

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
            .otp-group input { width: 44px; height: 50px; font-size: 1.2rem; }
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
    <a href="login.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Back to Login
    </a>

    <div class="form-box">

        <!-- ── STEP 1: Enter Email ── -->
        <div id="step1">
            <div class="icon-circle">
                <i class="fas fa-lock-open"></i>
            </div>

            <div class="form-header">
                <h1>Forgot password? 🔐</h1>
                <p>Enter your registered email and we'll send you a one-time verification code to reset your password.</p>
            </div>

            <!-- Steps -->
            <div class="steps">
                <div class="step active" id="s1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Email</div>
                </div>
                <div class="step-line" id="line1"></div>
                <div class="step" id="s2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Verify</div>
                </div>
                <div class="step-line" id="line2"></div>
                <div class="step" id="s3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Reset</div>
                </div>
            </div>

            <div class="alert" id="step1Alert">
                <i class="fas fa-exclamation-circle"></i>
                <span id="step1AlertMsg"></span>
            </div>

            <div class="field" id="field-email">
                <label for="email">Email Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-envelope fi"></i>
                    <input type="email" id="email" name="email" placeholder="you@email.com" autocomplete="email" required>
                </div>
                <p class="field-error">Please enter a valid email address.</p>
            </div>

            <button class="btn-primary" id="btnSendCode" onclick="sendCode()">
                <i class="fas fa-paper-plane"></i> Send Verification Code
            </button>

            <a href="login.php" class="btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Sign In
            </a>
        </div>

        <!-- ── STEP 2: Enter OTP ── -->
        <div id="step2" style="display:none;">
            <div class="icon-circle">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div class="form-header">
                <h1>Check your email 📩</h1>
                <p>We sent a 6-digit code to <strong id="emailDisplay"></strong>. Enter it below. The code expires in <strong>10 minutes</strong>.</p>
            </div>

            <div class="steps">
                <div class="step done" id="s1b">
                    <div class="step-circle"><i class="fas fa-check" style="font-size:11px"></i></div>
                    <div class="step-label">Email</div>
                </div>
                <div class="step-line done" id="line1b"></div>
                <div class="step active" id="s2b">
                    <div class="step-circle">2</div>
                    <div class="step-label">Verify</div>
                </div>
                <div class="step-line" id="line2b"></div>
                <div class="step" id="s3b">
                    <div class="step-circle">3</div>
                    <div class="step-label">Reset</div>
                </div>
            </div>

            <div class="alert" id="step2Alert">
                <i class="fas fa-exclamation-circle"></i>
                <span id="step2AlertMsg"></span>
            </div>

            <div class="field" style="margin-bottom:10px;">
                <label style="text-align:center; display:block; margin-bottom:14px;">Verification Code</label>
                <div class="otp-group" id="otpGroup">
                    <input type="text" maxlength="1" class="otp-input" data-index="0" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-input" data-index="1" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-input" data-index="2" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-input" data-index="3" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-input" data-index="4" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-input" data-index="5" inputmode="numeric" pattern="[0-9]">
                </div>
            </div>

            <div class="resend-row">
                Didn't receive it?
                <button id="resendBtn" onclick="resendCode()" disabled>Resend code (<span id="countdown">60</span>s)</button>
            </div>

            <button class="btn-primary" style="margin-top:24px;" onclick="verifyOtp()">
                <i class="fas fa-check-circle"></i> Verify Code
            </button>

            <button class="btn-ghost" onclick="goBack(1)">
                <i class="fas fa-arrow-left"></i> Change Email
            </button>
        </div>

        <!-- ── STEP 3: New Password ── -->
        <div id="step3" style="display:none;">
            <div class="icon-circle">
                <i class="fas fa-key"></i>
            </div>

            <div class="form-header">
                <h1>Set new password 🔑</h1>
                <p>Choose a strong password that you haven't used before.</p>
            </div>

            <div class="steps">
                <div class="step done">
                    <div class="step-circle"><i class="fas fa-check" style="font-size:11px"></i></div>
                    <div class="step-label">Email</div>
                </div>
                <div class="step-line done"></div>
                <div class="step done">
                    <div class="step-circle"><i class="fas fa-check" style="font-size:11px"></i></div>
                    <div class="step-label">Verify</div>
                </div>
                <div class="step-line done"></div>
                <div class="step active">
                    <div class="step-circle">3</div>
                    <div class="step-label">Reset</div>
                </div>
            </div>

            <div class="alert" id="step3Alert">
                <i class="fas fa-exclamation-circle"></i>
                <span id="step3AlertMsg"></span>
            </div>

            <!-- New Password -->
            <div class="field" id="field-newpass">
                <label for="newPassword">New Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-lock fi"></i>
                    <input type="password" id="newPassword" placeholder="Enter new password" oninput="checkStrength(this.value)">
                    <button type="button" class="eye-btn" onclick="toggleEye('newPassword', 'eyeBtn1')">
                        <i class="fas fa-eye" id="eyeBtn1"></i>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="strength-label" id="strengthLabel"></div>
                <p class="field-error">Password must be at least 8 characters.</p>
            </div>

            <!-- Confirm Password -->
            <div class="field" id="field-confirmpass">
                <label for="confirmPassword">Confirm Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-lock fi"></i>
                    <input type="password" id="confirmPassword" placeholder="Re-enter new password">
                    <button type="button" class="eye-btn" onclick="toggleEye('confirmPassword', 'eyeBtn2')">
                        <i class="fas fa-eye" id="eyeBtn2"></i>
                    </button>
                </div>
                <p class="field-error">Passwords do not match.</p>
            </div>

            <button class="btn-primary" onclick="resetPassword()">
                <i class="fas fa-rotate-right"></i> Reset Password
            </button>
        </div>

        <!-- ── SUCCESS SCREEN ── -->
        <div class="success-screen" id="successScreen">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2>Password Reset!</h2>
            <p>Your password has been successfully updated. You can now sign in with your new password.</p>
            <a href="login.php" class="btn-primary" style="text-decoration:none;">
                <i class="fas fa-arrow-right-to-bracket"></i> Back to Sign In
            </a>
        </div>

        <div class="form-footer" id="formFooter">
            By using this service, you agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
            of the E-Gov Portal, in compliance with the Data Privacy Act of 2012 (R.A. 10173).
        </div>
    </div>
</div>

<script>
    // ── Helpers ──
    function showAlert(stepNum, type, msg) {
        const el = document.getElementById('step' + stepNum + 'Alert');
        const msgEl = document.getElementById('step' + stepNum + 'AlertMsg');
        el.className = 'alert ' + type;
        msgEl.textContent = msg;
        el.style.display = 'flex';
    }
    function hideAlert(stepNum) {
        document.getElementById('step' + stepNum + 'Alert').style.display = 'none';
    }
    function validateEmail(val) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim()); }
    function setFieldError(id, hasError) {
        document.getElementById('field-' + id).classList.toggle('error', hasError);
    }
    function toggleEye(inputId, btnId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(btnId);
        if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
        else                           { input.type = 'password'; icon.className = 'fas fa-eye'; }
    }
    function showStep(n) {
        [1,2,3].forEach(i => document.getElementById('step'+i).style.display = 'none');
        document.getElementById('successScreen').style.display = 'none';
        document.getElementById('step'+n).style.display = 'block';
    }

    // ── Step 1: Send Code ──
    let currentEmail = '';

    function sendCode() {
        const email = document.getElementById('email').value.trim();
        hideAlert(1);
        if (!validateEmail(email)) {
            setFieldError('email', true);
            return;
        }
        setFieldError('email', false);

        const btn = document.getElementById('btnSendCode');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Sending…';

        fetch('forget_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=send_code&email=' + encodeURIComponent(email)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Verification Code';
            if (data.success) {
                currentEmail = email;
                document.getElementById('emailDisplay').textContent = email;
                showStep(2);
                startCountdown();
            } else {
                showAlert(1, 'error', data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Verification Code';
            showAlert(1, 'error', 'Could not reach the server. Please try again.');
        });
    }

    // ── OTP inputs ──
    document.querySelectorAll('.otp-input').forEach((input, idx, inputs) => {
        input.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val;
            if (val) {
                e.target.classList.add('filled');
                if (idx < inputs.length - 1) inputs[idx + 1].focus();
            } else {
                e.target.classList.remove('filled');
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                inputs[idx - 1].focus();
                inputs[idx - 1].classList.remove('filled');
            }
        });
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const paste = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            paste.split('').forEach((ch, i) => {
                if (inputs[idx + i]) {
                    inputs[idx + i].value = ch;
                    inputs[idx + i].classList.add('filled');
                }
            });
            if (inputs[idx + paste.length - 1]) inputs[idx + paste.length - 1].focus();
        });
    });

    // ── Countdown ──
    let timer;
    function startCountdown() {
        let sec = 60;
        const btn = document.getElementById('resendBtn');
        const cdEl = document.getElementById('countdown');
        btn.disabled = true;
        clearInterval(timer);
        timer = setInterval(() => {
            sec--;
            cdEl.textContent = sec;
            if (sec <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                btn.textContent = 'Resend code';
            }
        }, 1000);
    }
    function resendCode() {
        document.querySelectorAll('.otp-input').forEach(i => { i.value = ''; i.classList.remove('filled'); });
        hideAlert(2);

        fetch('forget_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=send_code&email=' + encodeURIComponent(currentEmail)
        })
        .then(res => res.json())
        .then(data => {
            startCountdown();
            if (data.success) {
                showAlert(2, 'success', 'A new code has been sent to your email.');
            } else {
                showAlert(2, 'error', data.message || 'Could not resend the code. Please try again.');
            }
        })
        .catch(() => {
            startCountdown();
            showAlert(2, 'error', 'Could not reach the server. Please try again.');
        });
    }

    // ── Step 2: Verify OTP ──
    function verifyOtp() {
        hideAlert(2);
        const otp = [...document.querySelectorAll('.otp-input')].map(i => i.value).join('');
        if (otp.length < 6) {
            showAlert(2, 'error', 'Please enter the complete 6-digit code.');
            return;
        }

        fetch('forget_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=verify_otp&otp=' + encodeURIComponent(otp)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showStep(3);
            } else {
                showAlert(2, 'error', data.message || 'Incorrect code. Please try again.');
            }
        })
        .catch(() => {
            showAlert(2, 'error', 'Could not reach the server. Please try again.');
        });
    }

    // ── Password strength ──
    function checkStrength(val) {
        const fill = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const levels = [
            { pct: '0%',   color: '#e53935', text: '' },
            { pct: '25%',  color: '#e53935', text: 'Weak' },
            { pct: '50%',  color: '#ff9800', text: 'Fair' },
            { pct: '75%',  color: '#fdd835', text: 'Good' },
            { pct: '100%', color: '#2e7d32', text: 'Strong 💪' },
        ];
        const lvl = levels[score];
        fill.style.width = lvl.pct;
        fill.style.background = lvl.color;
        label.textContent = lvl.text;
        label.style.color = lvl.color;
    }

    // ── Step 3: Reset Password ──
    function resetPassword() {
        hideAlert(3);
        const np = document.getElementById('newPassword').value;
        const cp = document.getElementById('confirmPassword').value;
        let valid = true;
        if (np.length < 8) { setFieldError('newpass', true); valid = false; }
        else setFieldError('newpass', false);
        if (np !== cp || !cp) { setFieldError('confirmpass', true); valid = false; }
        else setFieldError('confirmpass', false);
        if (!valid) return;

        const btn = document.querySelector('#step3 .btn-primary');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Updating…';

        fetch('forget_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=reset_password&password=' + encodeURIComponent(np) + '&confirm_password=' + encodeURIComponent(cp)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (data.success) {
                ['step1','step2','step3'].forEach(id => document.getElementById(id).style.display = 'none');
                document.getElementById('successScreen').style.display = 'block';
                document.getElementById('formFooter').style.display = 'none';
            } else {
                showAlert(3, 'error', data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            showAlert(3, 'error', 'Could not reach the server. Please try again.');
        });
    }

    function goBack(n) { showStep(n); }

    // ── Clear field errors on input ──
    document.getElementById('email').addEventListener('input', () => setFieldError('email', false));
    document.getElementById('newPassword').addEventListener('input', () => setFieldError('newpass', false));
    document.getElementById('confirmPassword').addEventListener('input', () => setFieldError('confirmpass', false));
</script>

</body>
</html>