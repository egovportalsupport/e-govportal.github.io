<?php
session_start();
$isLoggedIn = isset($_SESSION['validpage']) && $_SESSION['validpage'] === TRUE;
$firstName  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname'] ?? '') : '';
$lastName   = $isLoggedIn ? htmlspecialchars($_SESSION['lastname']  ?? '') : '';
$userEmail  = $isLoggedIn ? htmlspecialchars($_SESSION['email']     ?? '') : '';
$initials   = $isLoggedIn ? strtoupper(substr($firstName,0,1).substr($lastName,0,1)) : '';
$fullName   = trim("$firstName $lastName");

/* ── Handle form submission ── */
$formError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $subject && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        /* TODO: plug in your mailer / DB insert here */

        /* Pass sender info to thankyou page via session flash */
        $_SESSION['contact_name']    = htmlspecialchars($name);
        $_SESSION['contact_subject'] = htmlspecialchars($subject);
        $_SESSION['contact_email']   = htmlspecialchars($email);

        header('Location: thankyou.php');
        exit;
    } else {
        $formError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - E-Government Service Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="navbar.css">
    <style>
:root {
    --primary-color: #004aad;
    --secondary-color: #0077ff;
    --accent-color: #ffd700;
    --text-dark: #333333;
    --text-light: #666666;
    --bg-light: #f8f9fa;
    --white: #ffffff;
    --shadow: 0 10px 30px rgba(0,0,0,0.08);
    --transition: all 0.3s ease;
}
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; scroll-behavior: smooth; }
body { background-color: var(--bg-light); color: var(--text-dark); line-height: 1.6; }

/* ── HERO ── */
header {
    text-align: center; padding: 80px 20px 100px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: var(--white); position: relative; overflow: hidden;
}
header > *:not(.hero-circles) { position: relative; z-index: 1; }
header h1 { font-size: 42px; font-weight: 700; letter-spacing: -1px; margin-bottom: 15px; }
header p  { font-size: 17px; opacity: 0.9; max-width: 600px; margin: 0 auto; line-height: 1.7; }

/* ── Animated Hero Circles ── */
.hero-circles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
.hero-circles span { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.08); animation: floatCircle linear infinite; }
.hero-circles span:nth-child(1) { width:320px; height:320px; top:-90px;  left:-90px;  animation-duration:20s; }
.hero-circles span:nth-child(2) { width:190px; height:190px; top:50px;   right:8%;    animation-duration:13s; animation-delay:-5s; background:rgba(255,255,255,0.06); }
.hero-circles span:nth-child(3) { width:100px; height:100px; bottom:20px; left:18%;   animation-duration:9s;  animation-delay:-2s; background:rgba(255,215,0,0.13); }
.hero-circles span:nth-child(4) { width:230px; height:230px; bottom:-70px; right:-50px; animation-duration:17s; animation-delay:-8s; }
.hero-circles span:nth-child(5) { width:60px;  height:60px;  top:38%; left:6%;        animation-duration:7s;  animation-delay:-1s; background:rgba(255,215,0,0.1); }
.hero-circles span:nth-child(6) { width:140px; height:140px; top:15%; right:22%;      animation-duration:11s; animation-delay:-4s; background:rgba(255,255,255,0.05); }
@keyframes floatCircle {
    0%   { transform: translateY(0)     scale(1);    opacity:.7; }
    33%  { transform: translateY(-24px) scale(1.05); opacity:1;  }
    66%  { transform: translateY(13px)  scale(.96);  opacity:.8; }
    100% { transform: translateY(0)     scale(1);    opacity:.7; }
}
/* live badge */
.live-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3); border-radius: 50px; padding: 7px 18px; font-size: 12.5px; font-weight: 500; margin-bottom: 20px; letter-spacing: 0.3px; color: #fff; }
.live-dot { width: 9px; height: 9px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 0 0 rgba(74,222,128,0.6); animation: livePulse 1.8s ease-out infinite; flex-shrink: 0; }
@keyframes livePulse { 0% { box-shadow: 0 0 0 0 rgba(74,222,128,0.7); } 70% { box-shadow: 0 0 0 9px rgba(74,222,128,0); } 100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); } }

/* ── INFO CARDS ── */
.info-row {
    max-width: 1100px; margin: 0 auto 80px;
    padding: 0 20px;
    display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 24px; position: relative; z-index: 10;
}
.info-card {
    background: var(--white); border-radius: 22px;
    padding: 36px 24px; text-align: center;
    box-shadow: 0 12px 36px rgba(0,0,0,0.10);
    border-bottom: 4px solid transparent;
    transition: var(--transition); position: relative; overflow: hidden;
}
.info-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); opacity: 0; transition: opacity 0.3s; }
.info-card:hover { transform: translateY(-10px); border-bottom-color: var(--accent-color); box-shadow: 0 24px 48px rgba(0,74,173,0.14); }
.info-card:hover::before { opacity: 1; }
.info-icon {
    width: 68px; height: 68px; border-radius: 20px; margin: 0 auto 18px;
    background: rgba(0,74,173,0.08); color: var(--primary-color);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; transition: var(--transition);
}
.info-card:hover .info-icon { background: var(--primary-color); color: var(--white); border-radius: 50%; }
.info-card h3 { font-size: 16px; font-weight: 700; color: var(--primary-color); margin-bottom: 10px; }
.info-card p  { font-size: 13.5px; color: var(--text-light); line-height: 1.7; }
.info-card a  { color: var(--primary-color); text-decoration: none; font-weight: 500; }
.info-card a:hover { text-decoration: underline; }

/* ── MAIN CONTENT WRAPPER ── */
.contact-wrapper {
    max-width: 1100px; margin: 60px auto 80px;
    padding: 0 20px;
    display: grid; grid-template-columns: 1fr 1.5fr;
    gap: 36px; align-items: start;
}

/* ── LEFT PANEL ── */
.contact-aside { display: flex; flex-direction: column; gap: 24px; }

.aside-block {
    background: var(--white); border-radius: 22px;
    padding: 32px 28px; box-shadow: 0 8px 30px rgba(0,0,0,0.07);
    border-left: 4px solid var(--primary-color);
    transition: var(--transition);
}
.aside-block:hover { box-shadow: 0 16px 40px rgba(0,74,173,0.12); transform: translateY(-3px); }
.aside-block h3 {
    font-size: 17px; font-weight: 700; color: var(--primary-color);
    margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    padding-bottom: 14px; border-bottom: 1px solid #eef0f8;
}
.aside-block h3 i { width: 32px; height: 32px; background: rgba(0,74,173,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }

.hours-list { list-style: none; }
.hours-list li {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid #f3f4f8;
    font-size: 13.5px;
}
.hours-list li:last-child { border-bottom: none; }
.hours-list .day   { color: var(--text-dark); font-weight: 600; }
.hours-list .time  { color: var(--text-light); background: #f3f4f8; padding: 3px 10px; border-radius: 20px; font-size: 12.5px; }
.hours-list .closed { color: #e53935; font-weight: 700; background: #ffebee; padding: 3px 10px; border-radius: 20px; font-size: 12.5px; }

.social-row { display: flex; gap: 12px; flex-wrap: wrap; }
.social-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 50px;
    text-decoration: none; font-size: 13px; font-weight: 600;
    transition: var(--transition); border: 2px solid transparent;
}
.social-btn.fb   { background: #e7f0fd; color: #1877f2; border-color: #c5d9f8; }
.social-btn.fb:hover   { background: #1877f2; color: var(--white); border-color: #1877f2; transform: translateY(-2px); }
.social-btn.tw   { background: #e8f5fd; color: #1da1f2; border-color: #b8e4f9; }
.social-btn.tw:hover   { background: #1da1f2; color: var(--white); border-color: #1da1f2; transform: translateY(-2px); }
.social-btn.yt   { background: #fdeaea; color: #ff0000; border-color: #f8c3c3; }
.social-btn.yt:hover   { background: #ff0000; color: var(--white); border-color: #ff0000; transform: translateY(-2px); }

/* ── FORM PANEL ── */
.contact-form-panel {
    background: var(--white); border-radius: 22px;
    padding: 44px 40px; box-shadow: 0 8px 30px rgba(0,0,0,0.07);
    position: relative; overflow: hidden;
}
.contact-form-panel::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color)); }
.contact-form-panel h2 {
    font-size: 26px; font-weight: 700; color: var(--primary-color);
    margin-bottom: 6px;
}
.contact-form-panel .sub {
    font-size: 14px; color: var(--text-light); margin-bottom: 32px;
    padding-bottom: 24px; border-bottom: 1px solid #eef0f8;
}

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.form-group { display: flex; flex-direction: column; gap: 7px; margin-bottom: 20px; }
.form-group label { font-size: 13px; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; gap: 4px; }
.form-group label span { color: #e53935; }

.form-group input,
.form-group select,
.form-group textarea {
    padding: 13px 16px; border: 2px solid #e8eaf0;
    border-radius: 14px; font-size: 14px; font-family: 'Poppins', sans-serif;
    color: var(--text-dark); background: #fafbff;
    transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
    outline: none; resize: none;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(0,74,173,0.08);
    background: var(--white);
}
.form-group input:hover:not(:focus),
.form-group select:hover:not(:focus),
.form-group textarea:hover:not(:focus) { border-color: #c0c8e0; }
.form-group textarea { height: 148px; }

.btn-submit {
    width: 100%; padding: 15px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: var(--white); border: none; border-radius: 50px;
    font-size: 15px; font-weight: 700; font-family: 'Poppins', sans-serif;
    cursor: pointer; transition: var(--transition);
    display: flex; align-items: center; justify-content: center; gap: 10px;
    box-shadow: 0 8px 24px rgba(0,74,173,0.28); margin-top: 8px;
    letter-spacing: 0.3px;
}
.btn-submit:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(0,74,173,0.36); }
.btn-submit:active { transform: translateY(0); }

/* ── ALERTS ── */
.alert {
    padding: 14px 18px; border-radius: 12px;
    font-size: 14px; font-weight: 500;
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 24px;
}
.alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.alert-error   { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

/* ── MAP SECTION ── */
.map-section { max-width: 1100px; margin: 0 auto 80px; padding: 0 20px; }
.map-section h2 {
    font-size: 26px; font-weight: 700; color: var(--primary-color);
    margin-bottom: 6px;
}
.map-section p { font-size: 14px; color: var(--text-light); margin-bottom: 22px; }
.map-embed {
    border-radius: 22px; overflow: hidden;
    box-shadow: 0 12px 36px rgba(0,74,173,0.12); border: 3px solid var(--white);
    height: 360px; background: linear-gradient(135deg, #dde8f5, #c8d9f0);
    display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 14px; color: var(--text-light);
    position: relative;
}
.map-embed::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23004aad' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
.map-embed i { font-size: 48px; color: var(--primary-color); opacity: 0.4; position: relative; }
.map-embed p  { font-size: 15px; color: var(--primary-color); opacity: 0.6; font-weight: 500; position: relative; }

/* ── FOOTER ── */
footer { background: #001f4d; color: rgba(255,255,255,0.7); text-align: center; padding: 40px 20px; border-top: 4px solid var(--accent-color); }
.footer-inner { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.footer-logo-row { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.footer-logo-row i { color: var(--accent-color); }
footer p { font-size: 13.5px; }
footer .footer-sub { font-size: 11.5px; color: rgba(255,255,255,0.4); margin-top: 4px; letter-spacing: 0.4px; }

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .contact-wrapper { grid-template-columns: 1fr; }
    header { padding: 60px 20px 80px; }
    header h1 { font-size: 28px; letter-spacing: 0; }
}
@media (max-width: 600px) {
    header { padding: 60px 20px 80px; }
    header h1 { font-size: 22px; }
    .contact-form-panel { padding: 28px 20px; }
    .form-row { grid-template-columns: 1fr; }
    .info-row { margin-top: 24px; }
}
@media (max-width: 900px) { .nav-center { display: none; } .hamburger { display: flex; } .user-profile { display: none; } }
@media (max-width: 600px) { nav { padding: 14px 4%; } .nav-left h2 { font-size: 18px; } .nav-right .login-btn { display: none; } }
    </style>
</head>
<body>

<!-- ══════════════ NAVBAR ══════════════ -->
<nav>
    <div class="nav-left">
        <h2><i class="fa-solid fa-building-columns"></i> E-Gov Portal</h2>
    </div>
    <ul class="nav-center">
        <li><a href="index.php">Home</a></li>
        <li><a href="service.php">Services</a></li>
            <li><a href="schedule.php">Online Schedule</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php" class="active">Contact Us</a></li>
    </ul>
    <div class="nav-right">
        <?php if ($isLoggedIn): ?>
        <div class="user-profile" id="userProfile">
            <button class="profile-btn" id="profileBtn" aria-expanded="false" aria-haspopup="true">
                <div class="profile-avatar"><?= $initials ?></div>
                <span class="profile-name"><?= $firstName ?></span>
                <i class="fas fa-chevron-down profile-caret"></i>
            </button>
            <div class="profile-dropdown" id="profileDropdown" role="menu">
                <div class="dropdown-header">
                    <div class="dh-name"><?= $fullName ?></div>
                    <div class="dh-email"><?= $userEmail ?></div>
                    <div class="dh-badge"><i class="fas fa-circle-check"></i> Active Account</div>
                </div>
                <ul class="dropdown-menu">
                    <li><a href="track_application.php" role="menuitem"><i class="fas fa-location-dot"></i> Track Application</a></li>
                    <li class="divider-item"></li>
                    <li class="logout-item"><a href="login.php?logout=1" role="menuitem"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
                </ul>
            </div>
        </div>
        <?php else: ?>
        <a href="login.php" class="login-btn">Login <i class="fa-solid fa-arrow-right-to-bracket"></i></a>
        <?php endif; ?>
    </div>
    <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- ══════════════ MOBILE DRAWER ══════════════ -->
<div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Navigation menu">
    <div class="mobile-menu-backdrop" id="menuBackdrop"></div>
    <div class="mobile-menu-drawer">
        <div class="drawer-header">
            <h2><i class="fa-solid fa-building-columns"></i> E-Gov Portal</h2>
            <button class="drawer-close" id="drawerClose" aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?php if ($isLoggedIn): ?>
        <div class="drawer-user-section">
            <div class="drawer-avatar"><?= $initials ?></div>
            <div class="drawer-user-info">
                <div class="dui-name"><?= $fullName ?></div>
                <div class="dui-email"><?= $userEmail ?></div>
            </div>
        </div>
        <?php endif; ?>
        <ul class="drawer-nav">
            <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
            <li><a href="service.php"><i class="fa-solid fa-list-check"></i> Services</a></li>
                <li><a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Online Schedule</a></li>
            <li><a href="about.php"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
            <li><a href="contact.php" class="active"><i class="fa-solid fa-envelope"></i> Contact Us</a></li>
            <?php if ($isLoggedIn): ?>
            <li><a href="track_application.php"><i class="fa-solid fa-location-dot"></i> Track Application</a></li>
            <?php endif; ?>
        </ul>
        <div class="drawer-footer">
            <?php if ($isLoggedIn): ?>
            <a href="login.php?logout=1" class="login-btn" style="background:#e53935;">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
            <?php else: ?>
            <a href="login.php" class="login-btn">Login <i class="fa-solid fa-arrow-right-to-bracket"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══════════════ HERO ══════════════ -->
<header>
    <div class="hero-circles">
        <span></span><span></span><span></span>
        <span></span><span></span><span></span>
    </div>
    <div class="live-badge"><span class="live-dot"></span> Support Team is Online</div>
    <h1>Contact Us</h1>
    <p>Have a question or concern? We're here to help. Reach out through any of the channels below and our team will respond promptly.</p>
</header>

<!-- ══════════════ FORM + ASIDE ══════════════ -->
<div class="contact-wrapper">

    <!-- LEFT: hours + social -->
    <div class="contact-aside">
        <div class="aside-block">
            <h3><i class="fas fa-clock"></i> Office Hours</h3>
            <ul class="hours-list">
                <li><span class="day">Monday</span>    <span class="time">8:00 AM – 5:00 PM</span></li>
                <li><span class="day">Tuesday</span>   <span class="time">8:00 AM – 5:00 PM</span></li>
                <li><span class="day">Wednesday</span> <span class="time">8:00 AM – 5:00 PM</span></li>
                <li><span class="day">Thursday</span>  <span class="time">8:00 AM – 5:00 PM</span></li>
                <li><span class="day">Friday</span>    <span class="time">8:00 AM – 5:00 PM</span></li>
                <li><span class="day">Saturday</span>  <span class="closed">Closed</span></li>
                <li><span class="day">Sunday</span>    <span class="closed">Closed</span></li>
            </ul>
        </div>

        <div class="aside-block">
            <h3><i class="fas fa-share-nodes"></i> Follow Us</h3>
            <div class="social-row">
                <a href="#" class="social-btn fb"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="#" class="social-btn tw"><i class="fab fa-twitter"></i> Twitter</a>
                <a href="#" class="social-btn yt"><i class="fab fa-youtube"></i> YouTube</a>
            </div>
        </div>
    </div>

    <!-- RIGHT: contact form -->
    <div class="contact-form-panel">
        <h2>Send Us a Message</h2>
        <p class="sub">Fill in the form below and we'll get back to you as soon as possible.</p>

        <?php if ($formError): ?>
        <div class="alert alert-error">
            <i class="fas fa-circle-exclamation"></i>
            Please fill in all required fields with valid information.
        </div>
        <?php endif; ?>

        <form method="POST" action="contact.php" novalidate>
            <input type="hidden" name="contact_submit" value="1">

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name <span>*</span></label>
                    <input type="text" id="name" name="name"
                           placeholder="Juan Dela Cruz"
                           value="<?= $isLoggedIn ? $fullName : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span>*</span></label>
                    <input type="email" id="email" name="email"
                           placeholder="juan@email.com"
                           value="<?= $isLoggedIn ? $userEmail : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '') ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="subject">Subject <span>*</span></label>
                <select id="subject" name="subject" required>
                    <option value="" disabled <?= !isset($_POST['subject']) ? 'selected' : '' ?>>Select a subject…</option>
                    <option value="Application Status" <?= (isset($_POST['subject']) && $_POST['subject']==='Application Status') ? 'selected' : '' ?>>Application Status Inquiry</option>
                    <option value="Technical Support"  <?= (isset($_POST['subject']) && $_POST['subject']==='Technical Support')  ? 'selected' : '' ?>>Technical Support</option>
                    <option value="Accessibility"      <?= (isset($_POST['subject']) && $_POST['subject']==='Accessibility')      ? 'selected' : '' ?>>Accessibility Concern</option>
                    <option value="Account Issue"      <?= (isset($_POST['subject']) && $_POST['subject']==='Account Issue')      ? 'selected' : '' ?>>Account / Login Issue</option>
                    <option value="Feedback"           <?= (isset($_POST['subject']) && $_POST['subject']==='Feedback')           ? 'selected' : '' ?>>General Feedback</option>
                    <option value="Other"              <?= (isset($_POST['subject']) && $_POST['subject']==='Other')              ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message <span>*</span></label>
                <textarea id="message" name="message" placeholder="Describe your concern in detail…" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>
</div>

<!-- ══════════════ INFO CARDS ══════════════ -->
<div class="info-row">
    <div class="info-card">
        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
        <h3>Visit Us</h3>
        <p>2nd Floor, Government Center Building<br>Elliptical Road, Diliman<br>Quezon City, Philippines 1100</p>
    </div>
    <div class="info-card">
        <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
        <h3>Call Us</h3>
        <p>
            <a href="tel:+6328888-0000">(02) 8888-0000</a><br>
            <a href="tel:+6328888-0001">(02) 8888-0001</a><br>
            <small style="color:var(--text-light);">Mon – Fri, 8 AM – 5 PM</small>
        </p>
    </div>
    <div class="info-card">
        <div class="info-icon"><i class="fas fa-envelope"></i></div>
        <h3>Email Us</h3>
        <p>
            <a href="mailto:support@egov.ph">support@egov.ph</a><br>
            <a href="mailto:info@egov.ph">info@egov.ph</a><br>
            <small style="color:var(--text-light);">Response within 24–48 hours</small>
        </p>
    </div>
    <div class="info-card">
        <div class="info-icon"><i class="fas fa-headset"></i></div>
        <h3>Live Support</h3>
        <p>Chat with our accessibility support team available Monday to Friday from 8 AM to 5 PM PST.</p>
    </div>
</div>

<!-- ══════════════ FOOTER ══════════════ -->
<footer>
    <div class="footer-inner">
        <div class="footer-logo-row"><i class="fa-solid fa-building-columns"></i> E-Gov Portal</div>
        <p>Accessible Government Digital System &copy; 2026</p>
        <p class="footer-sub">Designed for Accessibility &amp; Inclusivity &mdash; Republic of the Philippines</p>
    </div>
</footer>

<?php include __DIR__ . '/accessibility_widget.php'; ?>

<script src="navbar.js"></script>
<script>
const userProfile = document.getElementById('userProfile');
const profileBtn  = document.getElementById('profileBtn');
if (userProfile && profileBtn) {
    profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const open = userProfile.classList.toggle('open');
        profileBtn.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function(e) {
        if (!userProfile.contains(e.target)) {
            userProfile.classList.remove('open');
            profileBtn.setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { userProfile.classList.remove('open'); profileBtn.setAttribute('aria-expanded','false'); }
    });
}
</script>
</body>
</html>