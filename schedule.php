<?php
session_start();
require_once __DIR__ . '/connection.php';

$isLoggedIn = isset($_SESSION["validpage"]) && $_SESSION["validpage"] === TRUE;
$firstName  = $isLoggedIn ? htmlspecialchars($_SESSION["firstname"] ?? "") : "";
$lastName   = $isLoggedIn ? htmlspecialchars($_SESSION["lastname"]  ?? "") : "";
$userEmail  = $isLoggedIn ? htmlspecialchars($_SESSION["email"]     ?? "") : "";
$initials   = $isLoggedIn ? strtoupper(substr($firstName,0,1).substr($lastName,0,1)) : "";
$fullName   = trim("$firstName $lastName");

// ═══════════════════════════════════════════════════════════
//  AJAX ENDPOINT — read-only slot availability
//  GET schedule.php?ajax=slots&date=YYYY-MM-DD&prefix=<service prefix>
//
//  Browse-only: returns how many people already hold a ticket for
//  each time slot on the given date, for the given agency, pulled
//  from the real appointment_queue table (same table written to by
//  track_application.php's queue_next endpoint). No booking happens
//  here. Capacity is a fixed assumption (SLOT_CAPACITY) since the
//  portal has no per-agency "max appointments per hour" setting yet.
// ═══════════════════════════════════════════════════════════
if (($_GET['ajax'] ?? '') === 'slots') {
    header('Content-Type: application/json');

    $date   = trim($_GET['date'] ?? '');
    $prefix = trim($_GET['prefix'] ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $prefix === '') {
        echo json_encode(['error' => 'Missing or invalid date/prefix', 'slots' => []]);
        exit;
    }

    if (!isset($Connection)) {
        echo json_encode(['error' => 'connection.php did not provide $Connection', 'slots' => []]);
        exit;
    }

    $dateEsc   = mysqli_real_escape_string($Connection, $date);
    $prefixEsc = mysqli_real_escape_string($Connection, $prefix);

    $booked = []; // appt_time => count
    $res = @mysqli_query(
        $Connection,
        "SELECT appt_time, COUNT(*) AS booked
         FROM appointment_queue
         WHERE appt_date = '$dateEsc' AND service_prefix = '$prefixEsc'
         GROUP BY appt_time"
    );

    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $booked[$r['appt_time']] = (int) $r['booked'];
        }
        echo json_encode(['slots' => $booked]);
    } else {
        // appointment_queue table not present yet (schema not run) —
        // report gracefully instead of failing the page.
        echo json_encode(['slots' => [], 'note' => 'No live data yet']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Schedule - E-Government Service Portal</title>
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
.nav-center a.active { color: var(--primary-color); }
.nav-center a.active::after { width: 100%; }

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

@media (max-width: 900px) { .nav-center { display: none; } .hamburger { display: flex; } }
@media (max-width: 600px) { nav { padding: 14px 4%; } .nav-left h2 { font-size: 18px; } .nav-right .login-btn { display: none; } }

/* Profile dropdown (post-login) — same pattern as the rest of the portal */
.user-profile { position: relative; display: flex; align-items: center; }
.profile-btn { display: flex; align-items: center; gap: 10px; background: none; border: 2px solid #e0e8f5; border-radius: 50px; padding: 6px 16px 6px 6px; cursor: pointer; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; color: var(--text-dark); }
.profile-btn:hover { border-color: var(--primary-color); background: #f0f5ff; }
.profile-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; letter-spacing: 0.5px; }
.profile-name { font-size: 14px; font-weight: 600; color: var(--text-dark); white-space: nowrap; max-width: 120px; overflow: hidden; text-overflow: ellipsis; }
.profile-caret { font-size: 11px; color: var(--text-light); transition: transform 0.3s; }
.user-profile.open .profile-caret { transform: rotate(180deg); }
.profile-dropdown { position: absolute; top: calc(100% + 12px); right: 0; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,74,173,0.15), 0 4px 16px rgba(0,0,0,0.08); min-width: 230px; z-index: 9999; opacity: 0; transform: translateY(-8px) scale(0.97); pointer-events: none; transition: opacity 0.2s ease, transform 0.2s ease; border: 1px solid #eef0f8; overflow: hidden; }
.user-profile.open .profile-dropdown { opacity: 1; transform: translateY(0) scale(1); pointer-events: all; }
.dropdown-header { padding: 18px 20px 14px; border-bottom: 1px solid #f0f2f8; background: linear-gradient(135deg, #f8faff, #eef3ff); }
.dropdown-header .dh-name { font-size: 15px; font-weight: 700; color: var(--primary-color); }
.dropdown-header .dh-email { font-size: 12px; color: var(--text-light); margin-top: 2px; word-break: break-all; }
.dropdown-header .dh-badge { display: inline-flex; align-items: center; gap: 5px; background: #e8f5e9; color: #2e7d32; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; margin-top: 8px; }
.dropdown-menu { padding: 8px 0; list-style: none; }
.dropdown-menu li a { display: flex; align-items: center; gap: 12px; padding: 11px 20px; text-decoration: none; font-size: 14px; font-weight: 500; color: var(--text-dark); transition: background 0.2s, color 0.2s; }
.dropdown-menu li a i { width: 18px; text-align: center; color: var(--primary-color); font-size: 13px; }
.dropdown-menu li a:hover { background: #f0f5ff; color: var(--primary-color); }
.dropdown-menu .divider-item { height: 1px; background: #f0f2f8; margin: 6px 0; }
.dropdown-menu .logout-item a { color: #e53935; }
.dropdown-menu .logout-item a i { color: #e53935; }
.dropdown-menu .logout-item a:hover { background: #fff5f5; color: #c62828; }
.drawer-user-section { padding: 16px 24px; border-bottom: 1px solid #f0f0f0; background: linear-gradient(135deg, #f8faff, #eef3ff); display: flex; align-items: center; gap: 12px; }
.drawer-avatar { width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.drawer-user-info .dui-name { font-size: 14px; font-weight: 700; color: var(--primary-color); }
.drawer-user-info .dui-email { font-size: 11px; color: var(--text-light); word-break: break-all; }
@media (max-width: 900px) { .user-profile { display: none; } }
@media (max-width: 600px) { .profile-name { display: none; } .profile-btn { padding: 4px; border: none; background: none; } }

/* ── Page header ── */
.page-header { text-align: center; padding: 70px 20px 90px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; position: relative; overflow: hidden; }
.page-header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%; }
.page-header h1 { font-size: 38px; margin-bottom: 12px; font-weight: 700; position: relative; z-index: 1; }
.page-header p { font-size: 16.5px; opacity: 0.92; max-width: 640px; margin: 0 auto; position: relative; z-index: 1; }

/* ── Live animated circles ── */
.hero-circles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
.hero-circles span { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.08); animation: floatCircle linear infinite; }
.hero-circles span:nth-child(1) { width:220px; height:220px; top:-60px;  left:-60px;  animation-duration:18s; }
.hero-circles span:nth-child(2) { width:140px; height:140px; top:20px;   right:10%;   animation-duration:12s; animation-delay:-4s; background:rgba(255,255,255,0.06); }
.hero-circles span:nth-child(3) { width:80px;  height:80px;  bottom:10px; left:15%;   animation-duration:9s;  animation-delay:-2s; background:rgba(255,215,0,0.12); }
.hero-circles span:nth-child(4) { width:170px; height:170px; bottom:-50px; right:-30px; animation-duration:15s; animation-delay:-7s; }
.hero-circles span:nth-child(5) { width:50px;  height:50px;  top:35%;  left:6%;     animation-duration:7s;  animation-delay:-1s; background:rgba(255,215,0,0.1); }
.hero-circles span:nth-child(6) { width:110px; height:110px; top:15%;  right:24%;   animation-duration:11s; animation-delay:-5s; background:rgba(255,255,255,0.05); }
@keyframes floatCircle {
    0%   { transform: translateY(0)     scale(1);    opacity:.7; }
    33%  { transform: translateY(-22px) scale(1.04); opacity:1;  }
    66%  { transform: translateY(12px)  scale(.97);  opacity:.8; }
    100% { transform: translateY(0)     scale(1);    opacity:.7; }
}

/* ── Live pulse dot badge ── */
.live-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3); border-radius: 50px; padding: 7px 18px; font-size: 12.5px; font-weight: 500; margin-bottom: 18px; letter-spacing: 0.3px; position: relative; z-index: 1; color: #fff; }
.live-dot { width: 9px; height: 9px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 0 0 rgba(74,222,128,0.6); animation: livePulse 1.8s ease-out infinite; flex-shrink: 0; }
@keyframes livePulse { 0% { box-shadow: 0 0 0 0 rgba(74,222,128,0.7); } 70% { box-shadow: 0 0 0 9px rgba(74,222,128,0); } 100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); } }

/* ── Layout ── */
.schedule-wrap { max-width: 1300px; margin: -30px auto 60px; padding: 0 5%; position: relative; }
.panel { background: var(--white); border-radius: 22px; box-shadow: var(--shadow); border: 1px solid #edf0f7; padding: 28px 26px; margin-bottom: 24px; }
.panel h2 { font-size: 15px; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; display: flex; align-items: center; gap: 9px; }
.panel h2 i { color: var(--primary-color); }
.panel .panel-sub { font-size: 12.5px; color: var(--text-light); margin-bottom: 18px; }

/* Agency chips */
.agency-chips { display: flex; flex-wrap: wrap; gap: 10px; }
.agency-chip { display: flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 50px; border: 1.5px solid #e3e8f2; background: var(--white); color: var(--text-dark); font-size: 13px; font-weight: 600; cursor: pointer; transition: var(--transition); white-space: nowrap; }
.agency-chip i { color: var(--primary-color); font-size: 12px; }
.agency-chip:hover { border-color: var(--primary-color); background: #f0f5ff; }
.agency-chip.active { background: var(--primary-color); border-color: var(--primary-color); color: white; }
.agency-chip.active i { color: var(--accent-color); }

/* Date rail */
.date-rail { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 6px; scrollbar-width: thin; }
.date-pill { flex: 0 0 auto; min-width: 76px; text-align: center; padding: 12px 10px; border-radius: 14px; border: 1.5px solid #e3e8f2; cursor: pointer; transition: var(--transition); background: var(--white); }
.date-pill .dp-dow { font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: var(--text-light); letter-spacing: 0.4px; }
.date-pill .dp-num { font-size: 19px; font-weight: 700; color: var(--text-dark); margin: 2px 0; }
.date-pill .dp-mon { font-size: 10.5px; color: var(--text-light); }
.date-pill:hover { border-color: var(--primary-color); }
.date-pill.active { background: var(--primary-color); border-color: var(--primary-color); }
.date-pill.active .dp-dow, .date-pill.active .dp-num, .date-pill.active .dp-mon { color: white; }

/* Slot grid */
.slot-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 14px; margin-top: 20px; }
.slot-card { border: 1.5px solid #e3e8f2; border-radius: 16px; padding: 16px 18px; transition: var(--transition); position: relative; }
.slot-card .sc-time { font-size: 14.5px; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
.slot-card .sc-time i { color: var(--primary-color); font-size: 12px; }
.sc-bar-track { height: 7px; border-radius: 4px; background: #eef1f7; overflow: hidden; margin-bottom: 10px; }
.sc-bar-fill { height: 100%; border-radius: 4px; transition: width 0.4s ease; }
.sc-status { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; padding: 4px 11px; border-radius: 20px; }
.sc-count { font-size: 11.5px; color: var(--text-light); margin-top: 8px; }

.status-available .sc-bar-fill { background: #2e7d32; }
.status-available .sc-status { background: #e8f5e9; color: #2e7d32; }
.status-filling .sc-bar-fill { background: #f9a825; }
.status-filling .sc-status { background: #fff8e1; color: #b7791f; }
.status-full .sc-bar-fill { background: #e53935; }
.status-full .sc-status { background: #fdecea; color: #c62828; }
.status-full { opacity: 0.85; }

.slot-empty-note { text-align: center; padding: 30px 10px; color: var(--text-light); font-size: 13.5px; grid-column: 1 / -1; }
.slot-empty-note i { display: block; font-size: 26px; margin-bottom: 8px; color: #cfd6e4; }

/* Legend + disclaimer */
.legend-row { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 18px; padding-top: 16px; border-top: 1px dashed #e6eaf3; }
.legend-item { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--text-light); }
.legend-dot { width: 9px; height: 9px; border-radius: 50%; }

.info-panel { background: #f0f5ff; border: 1px solid #dde6fb; border-radius: 18px; padding: 20px 24px; display: flex; gap: 16px; align-items: flex-start; }
.info-panel i { color: var(--primary-color); font-size: 20px; margin-top: 2px; }
.info-panel p { font-size: 13px; color: var(--text-dark); line-height: 1.6; }
.info-panel a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
.info-panel a:hover { text-decoration: underline; }

footer { background: #001f4d; border-top: 4px solid var(--accent-color); padding: 40px 20px; }
.footer-inner { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.footer-logo-row { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.footer-logo-row i { color: var(--accent-color); }
footer p { font-size: 13.5px; color: rgba(255,255,255,0.7); }
footer .footer-sub { font-size: 11.5px; color: rgba(255,255,255,0.4); margin-top: 4px; letter-spacing: 0.4px; }

@media (max-width: 768px) {
    .page-header { padding: 50px 20px 70px; }
    .page-header h1 { font-size: 26px; }
    .page-header p { font-size: 14px; }
    .panel { padding: 22px 18px; }
}
    </style>
</head>
<body>

    <nav>
        <div class="nav-left">
            <h2><i class="fa-solid fa-building-columns"></i> E-Gov Portal</h2>
        </div>
        <ul class="nav-center">
            <li><a href="index.php">Home</a></li>
            <li><a href="service.php">Services</a></li>
            <li><a href="schedule.php" class="active">Online Schedule</a></li>
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
                <li><a href="service.php"><i class="fa-solid fa-list-check"></i> Services</a></li>
                <li><a href="schedule.php" class="active"><i class="fa-solid fa-calendar-days"></i> Online Schedule</a></li>
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
        <div class="hero-circles">
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>
        <div class="live-badge">
            <span class="live-dot"></span>
            Live Availability &mdash; Updated in Real Time
        </div>
        <h1>Online Schedule</h1>
        <p>Check appointment availability across government agency offices before you apply — no login needed to browse.</p>
    </section>

    <div class="schedule-wrap">

        <div class="panel">
            <h2><i class="fa-solid fa-landmark"></i> Choose an agency</h2>
            <p class="panel-sub">Availability is shown separately for each agency's office window.</p>
            <div class="agency-chips" id="agencyChips"></div>
        </div>

        <div class="panel">
            <h2><i class="fa-regular fa-calendar"></i> Choose a date</h2>
            <p class="panel-sub">Showing the next available office days (Monday–Saturday).</p>
            <div class="date-rail" id="dateRail"></div>
        </div>

        <div class="panel">
            <h2><i class="fa-regular fa-clock"></i> Time slot availability</h2>
            <p class="panel-sub" id="slotSubtext">Loading live availability…</p>

            <div class="slot-grid" id="slotGrid">
                <div class="slot-empty-note"><i class="fa-solid fa-spinner fa-spin"></i>Checking availability…</div>
            </div>

            <div class="legend-row">
                <div class="legend-item"><span class="legend-dot" style="background:#2e7d32;"></span> Available</div>
                <div class="legend-item"><span class="legend-dot" style="background:#f9a825;"></span> Filling Up</div>
                <div class="legend-item"><span class="legend-dot" style="background:#e53935;"></span> Full</div>
            </div>
        </div>

        <div class="info-panel">
            <i class="fa-solid fa-circle-info"></i>
            <p>
                This page is for browsing only. To actually book a slot, submit your application under
                <a href="service.php">Services</a> — once it's verified and digitally signed, appointment
                scheduling unlocks automatically from <a href="track_application.php">Track Application</a>.
            </p>
        </div>

    </div>

    <footer>
        <div class="footer-inner">
            <div class="footer-logo-row"><i class="fa-solid fa-building-columns"></i> E-Gov Portal</div>
            <p>Accessible Government Digital System &copy; 2026</p>
            <p class="footer-sub">Designed for Accessibility &amp; Inclusivity &mdash; Republic of the Philippines</p>
        </div>
    </footer>

    <script src="navbar.js"></script>
    <script>
    // ═══════════════════════════════════════════════════════════
    //  ONLINE SCHEDULE — browse-only availability viewer
    //  Pulls real counts from appointment_queue via schedule.php's
    //  ajax=slots endpoint. No booking action lives on this page.
    // ═══════════════════════════════════════════════════════════
    const AGENCIES = [
        { prefix: 'tesda_',    name: 'TESDA Enrollment',          icon: 'fa-graduation-cap' },
        { prefix: 'bir_',      name: 'BIR TIN Registration',      icon: 'fa-file-invoice-dollar' },
        { prefix: 'dswd_',     name: 'DSWD Assistance',           icon: 'fa-hand-holding-heart' },
        { prefix: 'vid_',      name: 'Valid ID Application',      icon: 'fa-id-card' },
        { prefix: 'smf_',      name: 'SM Foundation Scholarship', icon: 'fa-book-open' },
        { prefix: 'ched_',     name: 'CHED Scholarship',          icon: 'fa-university' },
        { prefix: 'comelec_',  name: 'COMELEC Voter Registration',icon: 'fa-vote-yea' },
        { prefix: 'psa_',      name: 'PSA Certificate Request',   icon: 'fa-scroll' },
        { prefix: 'lgu_',      name: 'LGU Online Services',       icon: 'fa-landmark' },
        { prefix: 'lto_',      name: "LTO Driver's License",      icon: 'fa-car' },
        { prefix: 'aha_',      name: 'AHA Learning Center',       icon: 'fa-chalkboard-teacher' },
    ];

    const TIME_SLOTS = [
        '9:00 AM – 10:00 AM',
        '10:00 AM – 11:00 AM',
        '11:00 AM – 12:00 PM',
        '1:00 PM – 2:00 PM',
        '2:00 PM – 3:00 PM',
        '3:00 PM – 4:00 PM',
    ];

    // Typical per-agency, per-hour walk-in capacity used only to compute
    // an Available / Filling Up / Full indicator — the portal doesn't
    // have a per-agency capacity setting yet, so this is a fixed estimate.
    const SLOT_CAPACITY = 15;

    let activePrefix = AGENCIES[0].prefix;
    let activeDate   = null; // 'YYYY-MM-DD'

    function buildAgencyChips() {
        const wrap = document.getElementById('agencyChips');
        wrap.innerHTML = AGENCIES.map(a => `
            <button class="agency-chip${a.prefix === activePrefix ? ' active' : ''}" data-prefix="${a.prefix}">
                <i class="fa-solid ${a.icon}"></i> ${a.name}
            </button>
        `).join('');
        wrap.querySelectorAll('.agency-chip').forEach(btn => {
            btn.addEventListener('click', () => {
                activePrefix = btn.dataset.prefix;
                wrap.querySelectorAll('.agency-chip').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                loadSlots();
            });
        });
    }

    // Next 14 calendar days, skipping Sundays (offices closed).
    function buildDateRail() {
        const rail = document.getElementById('dateRail');
        const days = [];
        let d = new Date();
        while (days.length < 10) {
            d.setDate(d.getDate() + (days.length === 0 ? 0 : 1));
            if (d.getDay() !== 0) days.push(new Date(d));
        }
        activeDate = toDateVal(days[0]);
        rail.innerHTML = days.map((day, i) => {
            const val = toDateVal(day);
            return `
                <div class="date-pill${i === 0 ? ' active' : ''}" data-date="${val}">
                    <div class="dp-dow">${day.toLocaleDateString('en-US', { weekday: 'short' })}</div>
                    <div class="dp-num">${day.getDate()}</div>
                    <div class="dp-mon">${day.toLocaleDateString('en-US', { month: 'short' })}</div>
                </div>
            `;
        }).join('');
        rail.querySelectorAll('.date-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                activeDate = pill.dataset.date;
                rail.querySelectorAll('.date-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                loadSlots();
            });
        });
    }

    function toDateVal(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function statusFor(booked) {
        const pct = booked / SLOT_CAPACITY;
        if (pct >= 1)   return { cls: 'status-full',      label: 'Full' };
        if (pct >= 0.6) return { cls: 'status-filling',   label: 'Filling Up' };
        return              { cls: 'status-available', label: 'Available' };
    }

    async function loadSlots() {
        const grid = document.getElementById('slotGrid');
        const sub  = document.getElementById('slotSubtext');
        const agency = AGENCIES.find(a => a.prefix === activePrefix);

        grid.innerHTML = `<div class="slot-empty-note"><i class="fa-solid fa-spinner fa-spin"></i>Checking availability…</div>`;
        sub.textContent = `Loading ${agency.name} availability…`;

        let bookedBySlot = {};
        try {
            const res = await fetch(`schedule.php?ajax=slots&date=${encodeURIComponent(activeDate)}&prefix=${encodeURIComponent(activePrefix)}`);
            const data = await res.json();
            bookedBySlot = data.slots || {};
        } catch (e) {
            console.warn('Could not load live availability:', e);
        }

        sub.textContent = `${agency.name} — office hours for the selected date`;

        grid.innerHTML = TIME_SLOTS.map(slot => {
            const booked = bookedBySlot[slot] || 0;
            const st = statusFor(booked);
            const pct = Math.min(100, Math.round((booked / SLOT_CAPACITY) * 100));
            return `
                <div class="slot-card ${st.cls}">
                    <div class="sc-time"><i class="fa-regular fa-clock"></i> ${slot}</div>
                    <div class="sc-bar-track"><div class="sc-bar-fill" style="width:${pct}%;"></div></div>
                    <span class="sc-status">${st.label}</span>
                    <div class="sc-count">${booked} applicant${booked === 1 ? '' : 's'} already scheduled this hour</div>
                </div>
            `;
        }).join('');
    }

    buildAgencyChips();
    buildDateRail();
    loadSlots();

    // Profile dropdown toggle (same pattern used across the portal)
    const userProfile = document.getElementById('userProfile');
    const profileBtn  = document.getElementById('profileBtn');
    if (userProfile && profileBtn) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = userProfile.classList.toggle('open');
            profileBtn.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', function (e) {
            if (!userProfile.contains(e.target)) {
                userProfile.classList.remove('open');
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { userProfile.classList.remove('open'); profileBtn.setAttribute('aria-expanded', 'false'); }
        });
    }
    </script>
    <?php include __DIR__ . '/accessibility_widget.php'; ?>
</body>
</html>