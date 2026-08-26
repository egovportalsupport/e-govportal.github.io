<?php
session_start();
$isLoggedIn = isset($_SESSION["validpage"]) && $_SESSION["validpage"] === TRUE;
$firstName  = $isLoggedIn ? htmlspecialchars($_SESSION["firstname"] ?? "") : "";
$lastName   = $isLoggedIn ? htmlspecialchars($_SESSION["lastname"]  ?? "") : "";
$userEmail  = $isLoggedIn ? htmlspecialchars($_SESSION["email"]     ?? "") : "";
$initials   = $isLoggedIn ? strtoupper(substr($firstName,0,1).substr($lastName,0,1)) : "";
$fullName   = trim("$firstName $lastName");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - E-Government Service Portal</title>
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
    --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
    --primary: #004aad;
    --secondary: #0077ff;
}

* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; scroll-behavior: smooth; }
body { background-color: var(--bg-light); color: var(--text-dark); line-height: 1.6; }

nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 5%;
    background: var(--white);
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-left h2 { color: var(--primary-color); font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; white-space: nowrap; }

.nav-center { display: flex; list-style: none; gap: 30px; margin: 0; padding: 0; }

.nav-center a { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 15px; transition: var(--transition); position: relative; white-space: nowrap; }
.nav-center a::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -5px; left: 0; background-color: var(--primary-color); transition: var(--transition); }
.nav-center a:hover { color: var(--primary-color); }
.nav-center a:hover::after { width: 100%; }

.nav-right .login-btn { background: var(--primary-color); color: var(--white); padding: 10px 25px; border-radius: 50px; text-decoration: none; font-weight: 500; font-size: 14px; transition: var(--transition); box-shadow: 0 4px 10px rgba(0,74,173,0.2); white-space: nowrap; display: flex; align-items: center; gap: 8px; }
.nav-right .login-btn:hover { background: var(--secondary-color); transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,119,255,0.3); }

.hamburger { display: none; flex-direction: column; justify-content: center; align-items: center; gap: 5px; width: 40px; height: 40px; background: none; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; padding: 6px; transition: var(--transition); flex-shrink: 0; }
.hamburger:hover { border-color: var(--primary-color); background: #f0f5ff; }
.hamburger span { display: block; width: 20px; height: 2px; background: var(--text-dark); border-radius: 2px; transition: var(--transition); transform-origin: center; }
.hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

.mobile-menu { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; }
.mobile-menu-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.4); opacity: 0; transition: opacity 0.3s ease; }
.mobile-menu-drawer { position: absolute; top: 0; right: 0; width: 280px; height: 100%; background: var(--white); box-shadow: -4px 0 24px rgba(0,0,0,0.12); display: flex; flex-direction: column; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.25,0.46,0.45,0.94); overflow-y: auto; }
.mobile-menu.open { display: block; }

.drawer-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #f0f0f0; }
.drawer-header h2 { color: var(--primary-color); font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.drawer-close { width: 34px; height: 34px; background: #f5f5f5; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--text-light); transition: var(--transition); }
.drawer-close:hover { background: #ffeeee; color: #e53935; }

.drawer-nav { list-style: none; padding: 16px 0; flex: 1; }
.drawer-nav li a { display: flex; align-items: center; gap: 12px; padding: 14px 24px; text-decoration: none; font-size: 15px; font-weight: 500; color: var(--text-dark); transition: var(--transition); border-left: 3px solid transparent; }
.drawer-nav li a i { width: 20px; text-align: center; color: var(--primary-color); font-size: 0.95rem; }
.drawer-nav li a:hover { background: #f0f5ff; color: var(--primary-color); border-left-color: var(--primary-color); }
.drawer-nav li a.active { background: #eef3ff; color: var(--primary-color); font-weight: 600; border-left-color: var(--primary-color); }

.drawer-footer { padding: 20px 24px; border-top: 1px solid #f0f0f0; }
.drawer-footer .login-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 13px 20px; background: var(--primary-color); color: var(--white); text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 14px; transition: var(--transition); box-shadow: 0 4px 12px rgba(0,74,173,0.25); }
.drawer-footer .login-btn:hover { background: var(--secondary-color); transform: translateY(-1px); }

@media (max-width: 900px) { .nav-center { display: none; } .hamburger { display: flex; } .user-profile { display: none; } }
@media (max-width: 600px) { nav { padding: 14px 4%; } .nav-left h2 { font-size: 18px; } .nav-right .login-btn { display: none; } }

        .page-header { text-align: center; padding: 80px 20px 100px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; position: relative; overflow: hidden; }
        .page-header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%; }
        .page-header h1 { font-size: 42px; margin-bottom: 15px; font-weight: 700; position: relative; }
        .page-header p { font-size: 18px; opacity: 0.9; margin-bottom: 40px; position: relative; }

        .search-container { position: relative; max-width: 600px; margin: 0 auto; }
        .header-search { display: flex; align-items: center; background: white; border-radius: 50px; padding: 8px 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); transition: transform 0.3s ease; }
        .header-search:focus-within { transform: scale(1.02); }
        .header-search i { color: var(--primary); font-size: 18px; margin-right: 15px; flex-shrink: 0; }
        .header-search input { border: none; outline: none; flex: 1; font-size: 16px; color: var(--text-dark); min-width: 0; }

        .services { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 28px; padding: 50px 5%; max-width: 1400px; margin: auto; }

        /* ── Beautiful Service Cards ── */
        .service-card {
            background: white;
            padding: 0;
            border-radius: 22px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
            border: 1px solid #edf0f7;
            overflow: hidden;
            position: relative;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,74,173,0.14);
            border-color: rgba(0,74,173,0.15);
        }

        /* Top accent stripe — animated on hover */
        .service-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--card-accent-a, var(--primary-color)), var(--card-accent-b, var(--secondary-color)));
            opacity: 0.35;
            transition: opacity 0.3s ease, height 0.3s ease;
            z-index: 1;
        }
        .service-card:hover::before { opacity: 1; height: 5px; }

        /* Subtle shimmer glow on hover */
        .service-card::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at top left, rgba(0,74,173,0.04) 0%, transparent 70%);
            opacity: 0; transition: opacity 0.4s ease; border-radius: 22px; pointer-events: none;
        }
        .service-card:hover::after { opacity: 1; }

        /* Card top band with icon + category tag */
        .card-top {
            display: flex; align-items: flex-start; justify-content: space-between;
            padding: 26px 26px 0;
        }

        .icon-wrapper {
            width: 58px; height: 58px;
            background: var(--icon-bg, rgba(0,74,173,0.1));
            color: var(--icon-color, var(--primary-color));
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            transition: all 0.35s ease;
            flex-shrink: 0;
            box-shadow: 0 4px 12px var(--icon-shadow, rgba(0,74,173,0.15));
        }
        .service-card:hover .icon-wrapper {
            background: var(--icon-color, var(--primary-color));
            color: white;
            transform: scale(1.08) rotate(-4deg);
            box-shadow: 0 8px 20px var(--icon-shadow, rgba(0,74,173,0.3));
        }

        .card-tag {
            font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--tag-color, var(--primary-color));
            background: var(--tag-bg, rgba(0,74,173,0.08));
            border-radius: 20px; padding: 4px 12px;
            white-space: nowrap; line-height: 1.8;
        }

        /* Card body */
        .card-body { padding: 18px 26px 0; flex-grow: 1; }
        .service-card h3 { color: var(--text-dark); margin-bottom: 8px; font-size: 16.5px; font-weight: 700; line-height: 1.3; }
        .service-card p { font-size: 13.5px; color: var(--text-light); line-height: 1.65; margin: 0; }

        /* Divider */
        .card-divider { height: 1px; background: linear-gradient(90deg, #edf0f7, transparent); margin: 20px 26px 0; }

        /* Card footer */
        .card-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 26px 22px;
        }
        .card-meta { font-size: 11px; color: #aab0c0; display: flex; align-items: center; gap: 5px; }
        .card-meta i { font-size: 10px; }

        .apply-btn {
            background: var(--icon-color, var(--primary-color));
            color: white;
            border: none;
            padding: 9px 22px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            display: inline-flex; align-items: center; gap: 7px;
            box-shadow: 0 4px 12px var(--icon-shadow, rgba(0,74,173,0.25));
            white-space: nowrap;
        }
        .apply-btn i { font-size: 11px; transition: transform 0.3s ease; }
        .apply-btn:hover { filter: brightness(1.12); transform: translateX(3px); box-shadow: 0 6px 18px var(--icon-shadow, rgba(0,74,173,0.35)); }
        .apply-btn:hover i { transform: translateX(4px); }

        .no-results { text-align: center; padding: 60px 20px; grid-column: 1 / -1; color: var(--text-light); display: none; }
        .no-results i { font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.4; }

        footer { background: #002a66; color: white; text-align: center; padding: 30px; margin-top: 60px; }

        @media (max-width: 768px) {
            .page-header { padding: 60px 20px 80px; }
            .page-header h1 { font-size: 28px; }
            .page-header p { font-size: 15px; }
            .services { padding: 30px 4%; gap: 20px; }
        }
        @media (max-width: 480px) {
            .page-header h1 { font-size: 22px; }
            .services { grid-template-columns: 1fr; }
            .header-search input { font-size: 14px; }
        }

        /* ── LOGIN GATE MODAL ───────────────────── */
        #loginGateOverlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0, 20, 60, 0.55);
            backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        #loginGateOverlay.active { display: flex; }

        #loginGateModal {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 74, 173, 0.22), 0 6px 20px rgba(0,0,0,0.1);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
            animation: modalPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.85) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .lgate-top {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 36px 30px 28px;
            text-align: center;
            position: relative;
        }
        .lgate-icon-ring {
            width: 68px; height: 68px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px; color: #fff;
        }
        .lgate-top h2 { color: #fff; font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .lgate-top p  { color: rgba(255,255,255,0.85); font-size: 14px; line-height: 1.5; }

        .lgate-body { padding: 28px 30px 32px; text-align: center; }
        .lgate-service-name {
            display: inline-flex; align-items: center; gap: 8px;
            background: #eef3ff; color: var(--primary-color);
            border-radius: 30px; padding: 7px 18px;
            font-size: 13px; font-weight: 600; margin-bottom: 24px;
        }
        .lgate-service-name i { font-size: 12px; }

        .lgate-actions { display: flex; flex-direction: column; gap: 12px; }
        .lgate-btn-login {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: var(--primary-color); color: #fff;
            padding: 14px 20px; border-radius: 50px;
            text-decoration: none; font-weight: 600; font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 18px rgba(0,74,173,0.3);
        }
        .lgate-btn-login:hover { background: var(--secondary-color); transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,119,255,0.35); }

        .lgate-btn-register {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: #fff; color: var(--primary-color);
            padding: 13px 20px; border-radius: 50px;
            text-decoration: none; font-weight: 600; font-size: 15px;
            border: 2px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        .lgate-btn-register:hover { background: #f0f5ff; transform: translateY(-2px); }

        .lgate-cancel {
            background: none; border: none; cursor: pointer;
            color: var(--text-light); font-size: 13px;
            font-family: 'Poppins', sans-serif; font-weight: 500;
            margin-top: 4px; padding: 6px; transition: color 0.2s;
        }
        .lgate-cancel:hover { color: #e53935; }

        .lgate-close-x {
            position: absolute; top: 14px; right: 16px;
            background: rgba(255,255,255,0.18); border: none;
            width: 30px; height: 30px; border-radius: 50%;
            color: #fff; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .lgate-close-x:hover { background: rgba(255,255,255,0.35); }

        @media (max-width: 480px) {
            #loginGateModal { border-radius: 18px; }
            .lgate-top { padding: 28px 20px 22px; }
            .lgate-body { padding: 22px 20px 26px; }
        }
        /* ─────────────────────────────────────── */
        /* ── Animated Hero Circles ── */
        .hero-circles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
        .hero-circles span {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: floatCircle linear infinite;
        }
        .hero-circles span:nth-child(1) { width:340px; height:340px; top:-100px; left:-100px; animation-duration:20s; }
        .hero-circles span:nth-child(2) { width:200px; height:200px; top:50px;  right:8%;    animation-duration:13s; animation-delay:-5s; background:rgba(255,255,255,0.06); }
        .hero-circles span:nth-child(3) { width:110px; height:110px; bottom:20px; left:18%;  animation-duration:9s;  animation-delay:-2s; background:rgba(255,215,0,0.13); }
        .hero-circles span:nth-child(4) { width:240px; height:240px; bottom:-70px; right:-50px; animation-duration:17s; animation-delay:-8s; }
        .hero-circles span:nth-child(5) { width:65px;  height:65px;  top:38%;  left:6%;     animation-duration:7s;  animation-delay:-1s; background:rgba(255,215,0,0.1); }
        .hero-circles span:nth-child(6) { width:150px; height:150px; top:15%;  right:22%;   animation-duration:11s; animation-delay:-4s; background:rgba(255,255,255,0.05); }
        @keyframes floatCircle {
            0%   { transform: translateY(0)     scale(1);    opacity:.7; }
            33%  { transform: translateY(-24px) scale(1.05); opacity:1;  }
            66%  { transform: translateY(13px)  scale(.96);  opacity:.8; }
            100% { transform: translateY(0)     scale(1);    opacity:.7; }
        }

        /* ── Live pulse badge ── */
        .live-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.3); border-radius: 50px;
            padding: 7px 18px; font-size: 12.5px; font-weight: 500;
            margin-bottom: 20px; letter-spacing: 0.3px;
            position: relative; z-index: 1; color: #fff;
        }
        .live-dot {
            width: 9px; height: 9px; border-radius: 50%;
            background: #4ade80; box-shadow: 0 0 0 0 rgba(74,222,128,0.6);
            animation: livePulse 1.8s ease-out infinite;
            flex-shrink: 0;
        }
        @keyframes livePulse {
            0%   { box-shadow: 0 0 0 0   rgba(74,222,128,0.7); }
            70%  { box-shadow: 0 0 0 9px rgba(74,222,128,0);   }
            100% { box-shadow: 0 0 0 0   rgba(74,222,128,0);   }
        }

        /* ensure page-header content sits above circles */
        .page-header > *:not(.hero-circles) { position: relative; z-index: 1; }

        /* ── Footer polish ── */
        footer { background: #001f4d; border-top: 4px solid var(--accent-color); padding: 40px 20px; }
        .footer-inner { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .footer-logo-row { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .footer-logo-row i { color: var(--accent-color); }
        footer p { font-size: 13.5px; color: rgba(255,255,255,0.7); }
        footer .footer-sub { font-size: 11.5px; color: rgba(255,255,255,0.4); margin-top: 4px; letter-spacing: 0.4px; }

    </style>
</head>
<body>

    <nav>
        <div class="nav-left">
            <h2><i class="fa-solid fa-building-columns"></i> E-Gov Portal</h2>
        </div>
        <ul class="nav-center">
            <li><a href="index.php">Home</a></li>
            <li><a href="service.php" class="active">Services</a></li>
            <li><a href="schedule.php">Online Schedule</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
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
                <li><a href="service.php" class="active"><i class="fa-solid fa-list-check"></i> Services</a></li>
                <li><a href="schedule.php"><i class="fa-solid fa-calendar-check"></i> Online Schedule</a></li>
                <li><a href="about.php"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
                <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> Contact Us</a></li>
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

    <section class="page-header">
        <!-- Animated floating circles -->
        <div class="hero-circles">
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>

        <!-- Live status badge -->
        <div class="live-badge">
            <span class="live-dot"></span>
            Portal is Live &amp; Accepting Applications
        </div>

        <h1>Government Online Services</h1>
        <p>Find and apply for government services easily from home</p>
        <div class="search-container">
            <div class="header-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search services (e.g., ID, Tax, Scholarship)..." id="serviceSearch">
            </div>
        </div>
    </section>

    <section class="services" id="servicesContainer">

        <!-- Valid ID -->
        <div class="service-card" style="--icon-bg:rgba(0,74,173,0.1);--icon-color:#004aad;--icon-shadow:rgba(0,74,173,0.22);--tag-bg:rgba(0,74,173,0.08);--tag-color:#004aad;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-id-card"></i></div>
                <span class="card-tag">Identity</span>
            </div>
            <div class="card-body">
                <h3>Valid ID Application</h3>
                <p>Apply or request assistance for government valid identification.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="valid_id/valid_id.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- TESDA -->
        <div class="service-card" style="--icon-bg:rgba(16,108,44,0.1);--icon-color:#0d7a38;--icon-shadow:rgba(13,122,56,0.22);--tag-bg:rgba(16,108,44,0.08);--tag-color:#0d7a38;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-graduation-cap"></i></div>
                <span class="card-tag">Training</span>
            </div>
            <div class="card-body">
                <h3>TESDA Programs</h3>
                <p>Apply for vocational training and certification programs.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="tesda/tesda.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- BIR -->
        <div class="service-card" style="--icon-bg:rgba(180,50,20,0.1);--icon-color:#b43214;--icon-shadow:rgba(180,50,20,0.22);--tag-bg:rgba(180,50,20,0.08);--tag-color:#b43214;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <span class="card-tag">Taxation</span>
            </div>
            <div class="card-body">
                <h3>BIR Services</h3>
                <p>Register for Tax Identification Number (TIN) and other tax services.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="bir/bir.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- DSWD -->
        <div class="service-card" style="--icon-bg:rgba(155,60,160,0.1);--icon-color:#8e24aa;--icon-shadow:rgba(142,36,170,0.22);--tag-bg:rgba(142,36,170,0.08);--tag-color:#8e24aa;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-hand-holding-heart"></i></div>
                <span class="card-tag">Social Welfare</span>
            </div>
            <div class="card-body">
                <h3>DSWD Assistance</h3>
                <p>Apply for government social welfare programs and financial aid.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="dswd/dswd.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- SM Foundation -->
        <div class="service-card" style="--icon-bg:rgba(230,130,0,0.1);--icon-color:#e07b00;--icon-shadow:rgba(230,130,0,0.22);--tag-bg:rgba(230,130,0,0.08);--tag-color:#e07b00;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-book-open"></i></div>
                <span class="card-tag">Scholarship</span>
            </div>
            <div class="card-body">
                <h3>SM Foundation Scholarship</h3>
                <p>Scholarship opportunities for deserving students.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="sm/smf_scholarship.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- CHED -->
        <div class="service-card" style="--icon-bg:rgba(0,119,180,0.1);--icon-color:#0077b4;--icon-shadow:rgba(0,119,180,0.22);--tag-bg:rgba(0,119,180,0.08);--tag-color:#0077b4;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-university"></i></div>
                <span class="card-tag">Scholarship</span>
            </div>
            <div class="card-body">
                <h3>CHED Scholarship</h3>
                <p>Apply for CHED scholarship programs for higher education.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="ched/ched.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- AHA -->
        <div class="service-card" style="--icon-bg:rgba(0,150,136,0.1);--icon-color:#00897b;--icon-shadow:rgba(0,150,136,0.22);--tag-bg:rgba(0,150,136,0.08);--tag-color:#00897b;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-laptop-code"></i></div>
                <span class="card-tag">Education</span>
            </div>
            <div class="card-body">
                <h3>AHA Learning Center</h3>
                <p>Enroll in digital learning and skills training programs.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="aha/aha.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- LGU -->
        <div class="service-card" style="--icon-bg:rgba(55,71,130,0.1);--icon-color:#374782;--icon-shadow:rgba(55,71,130,0.22);--tag-bg:rgba(55,71,130,0.08);--tag-color:#374782;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-city"></i></div>
                <span class="card-tag">Local Gov't</span>
            </div>
            <div class="card-body">
                <h3>LGU Services</h3>
                <p>Access local government services such as permits and certifications.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="lgu/lgu.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- LTO -->
        <div class="service-card" style="--icon-bg:rgba(200,80,0,0.1);--icon-color:#c85000;--icon-shadow:rgba(200,80,0,0.22);--tag-bg:rgba(200,80,0,0.08);--tag-color:#c85000;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-car"></i></div>
                <span class="card-tag">Transport</span>
            </div>
            <div class="card-body">
                <h3>LTO Services</h3>
                <p>Driver's license application, renewal, and vehicle registration.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="lto/lto_apply.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- COMELEC -->
        <div class="service-card" style="--icon-bg:rgba(21,101,192,0.1);--icon-color:#1565c0;--icon-shadow:rgba(21,101,192,0.22);--tag-bg:rgba(21,101,192,0.08);--tag-color:#1565c0;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-vote-yea"></i></div>
                <span class="card-tag">Elections</span>
            </div>
            <div class="card-body">
                <h3>COMELEC Registration</h3>
                <p>Register as a voter or update voter information.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="comelec/comelec.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- PSA -->
        <div class="service-card" style="--icon-bg:rgba(51,105,30,0.1);--icon-color:#33691e;--icon-shadow:rgba(51,105,30,0.22);--tag-bg:rgba(51,105,30,0.08);--tag-color:#33691e;">
            <div class="card-top">
                <div class="icon-wrapper"><i class="fa-solid fa-certificate"></i></div>
                <span class="card-tag">Civil Registry</span>
            </div>
            <div class="card-body">
                <h3>PSA Certificates</h3>
                <p>Request birth, marriage, and death certificates online.</p>
            </div>
            <div class="card-divider"></div>
            <div class="card-footer">
                <span class="card-meta"><i class="fa-solid fa-clock"></i> Online</span>
                <a href="psa/psa.php" class="apply-btn">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="no-results" id="noResults">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>No services found. Try a different keyword.</p>
        </div>
    </section>

    <footer>
        <div class="footer-inner">
            <div class="footer-logo-row"><i class="fa-solid fa-building-columns"></i> E-Gov Portal</div>
            <p>Accessible Government Digital System &copy; 2026</p>
            <p class="footer-sub">Designed for Accessibility &amp; Inclusivity &mdash; Republic of the Philippines</p>
        </div>
    </footer>

    <!-- ── LOGIN GATE MODAL ── -->
    <div id="loginGateOverlay" role="dialog" aria-modal="true" aria-labelledby="lgateTitle">
        <div id="loginGateModal">
            <div class="lgate-top">
                <button class="lgate-close-x" id="lgateCloseX" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                <div class="lgate-icon-ring"><i class="fa-solid fa-lock"></i></div>
                <h2 id="lgateTitle">Login Required</h2>
                <p>You need to be logged in to apply for a government service.</p>
            </div>
            <div class="lgate-body">
                <div class="lgate-service-name" id="lgateServiceName">
                    <i class="fa-solid fa-file-circle-check"></i>
                    <span id="lgateServiceLabel">Service</span>
                </div>
                <div class="lgate-actions">
                    <a href="login.php" class="lgate-btn-login" id="lgateLoginBtn">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Login to Continue
                    </a>
                    <a href="register.php" class="lgate-btn-register">
                        <i class="fa-solid fa-user-plus"></i> Create an Account
                    </a>
                    <button class="lgate-cancel" id="lgateCancel">Cancel — go back</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ───────────────────── -->

    <script src="navbar.js"></script>
    <script>
        const searchInput = document.getElementById('serviceSearch');
        const cards = Array.from(document.querySelectorAll('#servicesContainer .service-card'));
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            let visible = 0;
            cards.forEach(card => {
                const match = card.querySelector('.card-body h3').innerText.toLowerCase().includes(query) ||
                              card.querySelector('.card-body p').innerText.toLowerCase().includes(query);
                card.style.display = match ? 'flex' : 'none';
                if (match) visible++;
            });
            noResults.style.display = visible === 0 ? 'block' : 'none';
        });

        // Profile dropdown toggle
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

        // ── LOGIN GATE ──────────────────────────────────────────────────
        (function () {
            // Read login state from PHP — true if session is active
            var isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

            if (isLoggedIn) return; // logged in → nothing to intercept

            var overlay        = document.getElementById('loginGateOverlay');
            var serviceLabel   = document.getElementById('lgateServiceLabel');
            var loginBtn       = document.getElementById('lgateLoginBtn');
            var closeX         = document.getElementById('lgateCloseX');
            var cancelBtn      = document.getElementById('lgateCancel');

            function openGate(serviceName, targetHref) {
                // Show which service the user was trying to open
                serviceLabel.textContent = serviceName;
                // After login, redirect back to service page then re-click won't be needed
                // (simple approach: go to login, return to service.php)
                loginBtn.href = 'login.php?redirect=service.php';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                closeX.focus();
            }

            function closeGate() {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            // Intercept every Apply Now button
            document.querySelectorAll('.apply-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    var href = btn.getAttribute('href');
                    // Allow "#" links and non-real pages to also be gated
                    e.preventDefault();
                    var serviceName = btn.closest('.service-card')
                                        .querySelector('.card-body h3').textContent.trim();
                    openGate(serviceName, href);
                });
            });

            // Close on X button
            closeX.addEventListener('click', closeGate);
            // Close on Cancel button
            cancelBtn.addEventListener('click', closeGate);
            // Close on backdrop click
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeGate();
            });
            // Close on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('active')) closeGate();
            });
        })();
        // ────────────────────────────────────────────────────────────────
    </script>
    <?php include __DIR__ . '/accessibility_widget.php'; ?>
</body>
</html>