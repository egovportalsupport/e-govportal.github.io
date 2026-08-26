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
    <title>About Us - E-Government Service Portal</title>
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

        header { text-align: center; padding: 80px 20px 100px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: var(--white); position: relative; overflow: hidden; }
        header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%; }
        header h1 { font-size: 42px; margin-bottom: 15px; font-weight: 700; letter-spacing: -1px; position: relative; }
        header p { font-size: 17px; opacity: 0.9; max-width: 700px; margin: 0 auto; line-height: 1.7; position: relative; }

        .about-section { max-width: 1200px; margin: -50px auto 60px; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; position: relative; z-index: 10; }

        .about-card { background: var(--white); padding: 40px 30px; border-radius: 20px; box-shadow: var(--shadow); transition: transform 0.3s, box-shadow 0.3s; border-top: 4px solid transparent; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .about-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); border-top: 4px solid var(--primary); }

        .about-icon { width: 80px; height: 80px; background: rgba(0,74,173,0.1); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 25px; transition: 0.3s; }
        .about-card:hover .about-icon { background: var(--primary); color: var(--white); }

        .about-card h2 { color: var(--primary); margin-bottom: 15px; font-size: 24px; font-weight: 600; }
        .about-card p { line-height: 1.7; font-size: 15px; color: var(--text-light); }

        .stats-section { background: var(--white); padding: 60px 20px; margin-top: 60px; text-align: center; }
        .stats-container { max-width: 1200px; margin: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 30px; }
        .stat-item h3 { font-size: 42px; color: var(--primary); margin-bottom: 10px; font-weight: 700; }
        .stat-item p { color: var(--text-light); font-size: 16px; }

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
        /* ── Live pulse badge ── */
        .live-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3); border-radius: 50px; padding: 7px 18px; font-size: 12.5px; font-weight: 500; margin-bottom: 20px; letter-spacing: 0.3px; position: relative; z-index: 1; color: #fff; }
        .live-dot { width: 9px; height: 9px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 0 0 rgba(74,222,128,0.6); animation: livePulse 1.8s ease-out infinite; flex-shrink: 0; }
        @keyframes livePulse { 0% { box-shadow: 0 0 0 0 rgba(74,222,128,0.7); } 70% { box-shadow: 0 0 0 9px rgba(74,222,128,0); } 100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); } }
        header > *:not(.hero-circles) { position: relative; z-index: 1; }

        /* ── About cards polish ── */
        .about-card { position: relative; overflow: hidden; }
        .about-card::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); opacity: 0; transition: opacity 0.3s ease; }
        .about-card:hover::after { opacity: 1; }
        .about-icon { border-radius: 22px; }

        /* ── Stats section polish ── */
        .stats-section { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 70px 20px; margin-top: 60px; text-align: center; }
        .stats-section .section-label { color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 500; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 48px; }
        .stats-container { max-width: 1000px; margin: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 30px; }
        .stat-item { background: rgba(255,255,255,0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; padding: 32px 20px; transition: var(--transition); }
        .stat-item:hover { background: rgba(255,255,255,0.2); transform: translateY(-6px); }
        .stat-item h3 { font-size: 40px; color: var(--accent-color); margin-bottom: 8px; font-weight: 700; }
        .stat-item p { color: rgba(255,255,255,0.85); font-size: 14px; font-weight: 500; }

        /* ── Why Choose Us ── */
        .why-section { padding: 90px 20px; background: var(--white); }
        .why-inner { max-width: 1100px; margin: auto; }
        .why-header { text-align: center; margin-bottom: 56px; }
        .why-header .section-title { font-size: 32px; color: var(--primary-color); margin-bottom: 12px; font-weight: 700; }
        .why-header .section-subtitle { color: var(--text-light); font-size: 15px; max-width: 560px; margin: 0 auto; }
        .why-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
        .why-item { display: flex; gap: 20px; align-items: flex-start; background: var(--bg-light); border-radius: 18px; padding: 28px 24px; border: 2px solid transparent; transition: var(--transition); }
        .why-item:hover { border-color: var(--primary-color); background: #eef3ff; transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,74,173,0.1); }
        .why-num { font-size: 28px; font-weight: 800; color: var(--accent-color); line-height: 1; min-width: 42px; -webkit-text-stroke: 1px var(--primary-color); }
        .why-body h4 { font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 7px; }
        .why-body p { font-size: 13.5px; color: var(--text-light); line-height: 1.65; }

        /* ── Testimonials ── */
        .testimonial-section { padding: 90px 20px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: var(--white); text-align: center; position: relative; overflow: hidden; }
        .testimonial-section .section-title { color: var(--white); font-size: 32px; font-weight: 700; margin-bottom: 12px; position: relative; z-index: 1; }
        .testimonial-section .section-subtitle { color: rgba(255,255,255,0.8); font-size: 15px; max-width: 540px; margin: 0 auto 52px; position: relative; z-index: 1; }
        .testi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; max-width: 1050px; margin: auto; position: relative; z-index: 1; }
        .testi-card { background: rgba(255,255,255,0.13); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.22); border-radius: 22px; padding: 32px 28px; text-align: left; transition: var(--transition); }
        .testi-card:hover { background: rgba(255,255,255,0.22); transform: translateY(-6px); }
        .testi-stars { color: var(--accent-color); font-size: 14px; letter-spacing: 2px; margin-bottom: 16px; }
        .testi-text { font-size: 14px; color: rgba(255,255,255,0.9); line-height: 1.75; margin-bottom: 24px; font-style: italic; }
        .testi-author { display: flex; align-items: center; gap: 14px; }
        .testi-avatar { width: 44px; height: 44px; min-width: 44px; border-radius: 50%; background: var(--accent-color); color: var(--text-dark); font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .testi-author strong { display: block; font-size: 14px; color: var(--white); }
        .testi-author span { font-size: 12px; color: rgba(255,255,255,0.65); }

        /* ── Partner Agencies ── */
        .partners-section { padding: 80px 20px; background: var(--bg-light); text-align: center; }
        .partners-section .section-title { font-size: 32px; color: var(--primary-color); margin-bottom: 12px; font-weight: 700; }
        .partners-section .section-subtitle { color: var(--text-light); font-size: 15px; max-width: 560px; margin: 0 auto 48px; }
        .partners-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 16px; max-width: 900px; margin: auto; }
        .partner-badge { display: flex; align-items: center; gap: 10px; background: var(--white); border: 2px solid rgba(0,74,173,0.12); border-radius: 50px; padding: 14px 24px; font-size: 15px; font-weight: 600; color: var(--text-dark); box-shadow: 0 4px 14px rgba(0,0,0,0.05); transition: var(--transition); cursor: default; }
        .partner-badge i { color: var(--primary-color); font-size: 16px; }
        .partner-badge:hover { border-color: var(--primary-color); background: #eef3ff; color: var(--primary-color); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,74,173,0.12); }

        /* ── Footer ── */
        footer { background: #001f4d; color: rgba(255,255,255,0.7); text-align: center; padding: 40px 20px; margin-top: 0; border-top: 4px solid var(--accent-color); }
        footer p { font-size: 13.5px; }
        .footer-inner { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .footer-logo-row { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .footer-logo-row i { color: var(--accent-color); }
        footer .footer-sub { font-size: 11.5px; color: rgba(255,255,255,0.4); margin-top: 4px; letter-spacing: 0.4px; }

        @media (max-width: 768px) {
            header { padding: 60px 20px 80px; }
            header h1 { font-size: 28px; letter-spacing: 0; }
            header p { font-size: 15px; }
            .about-section { margin-top: 30px; }
            .stat-item h3 { font-size: 34px; }
        }
        @media (max-width: 480px) {
            header h1 { font-size: 22px; }
            .about-card { padding: 30px 20px; }
            .about-card h2 { font-size: 20px; }
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
            <li><a href="schedule.php">Online Schedule</a></li>
            <li><a href="about.php" class="active">About Us</a></li>
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
                        <li><a href="track-application.html" role="menuitem"><i class="fas fa-location-dot"></i> Track Application</a></li>
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
                <li><a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Online Schedule</a></li>
                <li><a href="about.php" class="active"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
                <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> Contact Us</a></li>
                <?php if ($isLoggedIn): ?>
                <li><a href="track-application.html"><i class="fa-solid fa-location-dot"></i> Track Application</a></li>
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

    <header>
        <div class="hero-circles">
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>
        <div class="live-badge"><span class="live-dot"></span> Portal is Live &amp; Accepting Applications</div>
        <h1>About E-Government Service Portal</h1>
        <p>Our mission is to provide accessible, inclusive, and digital government services to every citizen, making government interaction easy, fast, and reliable for all, including mobility-impaired individuals.</p>
    </header>

    <section class="about-section">
        <div class="about-card">
            <div class="about-icon"><i class="fas fa-users"></i></div>
            <h2>Our Mission</h2>
            <p>To provide seamless digital access to government services, ensuring inclusivity and convenience for all citizens, particularly those with mobility challenges.</p>
        </div>
        <div class="about-card">
            <div class="about-icon"><i class="fas fa-lightbulb"></i></div>
            <h2>Our Vision</h2>
            <p>To become a leading government digital platform recognized for accessibility, efficiency, and citizen satisfaction in all digital interactions.</p>
        </div>
        <div class="about-card">
            <div class="about-icon"><i class="fas fa-shield-alt"></i></div>
            <h2>Our Values</h2>
            <p>We value transparency, security, accessibility, and user-centric design to ensure our citizens can trust and rely on our digital services anytime.</p>
        </div>
    </section>

    <section class="stats-section">
        <p class="section-label">Portal at a Glance</p>
        <div class="stats-container">
            <div class="stat-item"><h3>50+</h3><p>Services Available</p></div>
            <div class="stat-item"><h3>10k+</h3><p>Active Users</p></div>
            <div class="stat-item"><h3>99%</h3><p>Accessibility Score</p></div>
            <div class="stat-item"><h3>24/7</h3><p>Support System</p></div>
        </div>
    </section>

    <!-- ── Why Choose Us ── -->
    <section class="why-section">
        <div class="why-inner">
            <div class="why-header">
                <h2 class="section-title">Why Citizens Choose Us</h2>
                <p class="section-subtitle">Thousands of Filipinos trust the E-Gov Portal for fast, secure, and inclusive government transactions — here's why.</p>
            </div>
            <div class="why-grid">
                <div class="why-item">
                    <div class="why-num">01</div>
                    <div class="why-body">
                        <h4>No More Long Queues</h4>
                        <p>Apply for government services from home, eliminating the need to travel to agency offices and wait in long lines.</p>
                    </div>
                </div>
                <div class="why-item">
                    <div class="why-num">02</div>
                    <div class="why-body">
                        <h4>Real-Time Status Updates</h4>
                        <p>Track every application in real time. Get notified instantly when your status changes — no follow-up calls needed.</p>
                    </div>
                </div>
                <div class="why-item">
                    <div class="why-num">03</div>
                    <div class="why-body">
                        <h4>Safe & Private by Design</h4>
                        <p>Your data is protected under RA 10173 (Data Privacy Act). We use encrypted sessions and never share your information.</p>
                    </div>
                </div>
                <div class="why-item">
                    <div class="why-num">04</div>
                    <div class="why-body">
                        <h4>Designed for Everyone</h4>
                        <p>Built with mobility-impaired citizens in mind. Every interaction is accessible via keyboard, screen reader, and assistive devices.</p>
                    </div>
                </div>
                <div class="why-item">
                    <div class="why-num">05</div>
                    <div class="why-body">
                        <h4>One Portal, All Agencies</h4>
                        <p>Access 50+ government services from multiple agencies through a single unified account — no more juggling different platforms.</p>
                    </div>
                </div>
                <div class="why-item">
                    <div class="why-num">06</div>
                    <div class="why-body">
                        <h4>Always Available</h4>
                        <p>The portal is online 24/7, so you can submit or check applications anytime — even outside government office hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Testimonials ── -->
    <section class="testimonial-section">
        <div class="hero-circles"><span></span><span></span><span></span></div>
        <h2 class="section-title">What Citizens Are Saying</h2>
        <p class="section-subtitle">Real feedback from Filipinos who've used the portal to access government services.</p>
        <div class="testi-grid">
            <div class="testi-card">
                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="testi-text">"As a wheelchair user, this portal changed everything for me. I can now apply for my PWD benefits without needing someone to accompany me to the office."</p>
                <div class="testi-author">
                    <div class="testi-avatar">MR</div>
                    <div><strong>Maria R.</strong><span>PWD ID Applicant, Quezon City</span></div>
                </div>
            </div>
            <div class="testi-card">
                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="testi-text">"The real-time tracking feature gave me peace of mind. I always knew where my application stood — no need to call the office repeatedly."</p>
                <div class="testi-author">
                    <div class="testi-avatar">JD</div>
                    <div><strong>Jose D.</strong><span>Senior Citizen Benefit, Cebu</span></div>
                </div>
            </div>
            <div class="testi-card">
                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="testi-text">"Very simple and clear. Even my lola was able to submit her application with minimal help. The large text option made a huge difference."</p>
                <div class="testi-author">
                    <div class="testi-avatar">AL</div>
                    <div><strong>Ana L.</strong><span>Social Assistance, Davao</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Partner Agencies Banner ── -->
    <section class="partners-section">
        <h2 class="section-title">Government Agencies We Work With</h2>
        <p class="section-subtitle">The portal is connected to key national agencies so you can access multiple services under one roof.</p>
        <div class="partners-grid">
            <div class="partner-badge"><i class="fas fa-briefcase-medical"></i><span>PhilHealth</span></div>
            <div class="partner-badge"><i class="fas fa-hand-holding-heart"></i><span>DSWD</span></div>
            <div class="partner-badge"><i class="fas fa-id-card"></i><span>PSA</span></div>
            <div class="partner-badge"><i class="fas fa-graduation-cap"></i><span>DepEd</span></div>
            <div class="partner-badge"><i class="fas fa-piggy-bank"></i><span>SSS</span></div>
            <div class="partner-badge"><i class="fas fa-umbrella"></i><span>GSIS</span></div>
            <div class="partner-badge"><i class="fas fa-building"></i><span>PAGIBIG</span></div>
            <div class="partner-badge"><i class="fas fa-road"></i><span>DPWH</span></div>
        </div>
    </section>

    <footer>
        <div class="footer-inner">
            <div class="footer-logo-row"><i class="fa-solid fa-building-columns"></i> E-Gov Portal</div>
            <p>Accessible Government Digital System &copy; 2026</p>
            <p class="footer-sub">Designed for Accessibility &amp; Inclusivity &mdash; Republic of the Philippines</p>
        </div>
    </footer>

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
    <?php include __DIR__ . '/accessibility_widget.php'; ?>
</body>
</html>