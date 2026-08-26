<?php
session_start();
$isLoggedIn = isset($_SESSION['validpage']) && $_SESSION['validpage'] === TRUE;
$firstName  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname'] ?? '') : '';
$lastName   = $isLoggedIn ? htmlspecialchars($_SESSION['lastname']  ?? '') : '';
$userEmail  = $isLoggedIn ? htmlspecialchars($_SESSION['email']     ?? '') : '';
$initials   = $isLoggedIn ? strtoupper(substr($firstName,0,1).substr($lastName,0,1)) : '';
$fullName   = trim("$firstName $lastName");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>An E-Government Service Portal with Built-in Accessibility Tools for Mobility-Impaired Citizens</title>
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
}
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; scroll-behavior: smooth; }
body { background-color: var(--white); color: var(--text-dark); line-height: 1.6; }

        header {
            position: relative; text-align: center; padding: 120px 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white); overflow: hidden;
        }
        header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%; }
        header h1 { font-size: 42px; margin-bottom: 20px; font-weight: 700; letter-spacing: -1px; }
        header p { font-size: 18px; max-width: 600px; margin: 0 auto 40px; opacity: 0.9; font-weight: 300; }
        .hero-buttons { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
        .btn { padding: 14px 35px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: var(--transition); display: inline-flex; align-items: center; gap: 10px; font-size: 15px; }
        .btn-primary { background: var(--white); color: var(--primary-color); }
        .btn-primary:hover { background: var(--accent-color); color: var(--text-dark); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .btn-outline { border: 2px solid var(--white); color: var(--white); }
        .btn-outline:hover { background: var(--white); color: var(--primary-color); }
        .features { padding: 80px 20px; background: var(--bg-light); text-align: center; }
        .section-title { font-size: 32px; color: var(--primary-color); margin-bottom: 15px; font-weight: 700; }
        .section-subtitle { color: var(--text-light); margin-bottom: 60px; max-width: 600px; margin-left: auto; margin-right: auto; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 30px; max-width: 1200px; margin: auto; }
        .feature-card { background: var(--white); padding: 40px 30px; border-radius: 20px; box-shadow: var(--shadow); transition: var(--transition); border-bottom: 4px solid transparent; }
        .feature-card:hover { transform: translateY(-10px); border-bottom: 4px solid var(--accent-color); }
        .icon-box { width: 70px; height: 70px; background: rgba(0,74,173,0.1); color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 25px; transition: var(--transition); }
        .feature-card:hover .icon-box { background: var(--primary-color); color: var(--white); }
        .feature-card h3 { font-size: 20px; margin-bottom: 15px; color: var(--text-dark); }
        .feature-card p { color: var(--text-light); font-size: 15px; }
        footer { background: #001f4d; color: rgba(255,255,255,0.7); text-align: center; padding: 40px 20px; border-top: 4px solid var(--accent-color); }
        footer p { font-size: 13.5px; }
        .footer-inner { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .footer-logo-row { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .footer-logo-row i { color: var(--accent-color); }
        footer .footer-sub { font-size: 11.5px; color: rgba(255,255,255,0.4); margin-top: 4px; letter-spacing: 0.4px; }
        @media (max-width: 768px) { header { padding: 80px 20px; } header h1 { font-size: 28px; letter-spacing: 0; } header p { font-size: 15px; } .btn { padding: 12px 24px; font-size: 14px; } .section-title { font-size: 26px; } .feature-card { padding: 30px 20px; } }
        @media (max-width: 480px) { header h1 { font-size: 22px; } .hero-buttons { flex-direction: column; align-items: center; } .btn { width: 100%; max-width: 280px; justify-content: center; } }

        /* ── How It Works ── */
        .how-it-works { padding: 80px 20px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: var(--white); text-align: center; }
        .how-it-works .section-title { color: var(--white); }
        .how-it-works .section-subtitle { color: rgba(255,255,255,0.8); margin-bottom: 60px; }
        .steps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 30px; max-width: 1100px; margin: auto; position: relative; }
        .step-card { background: rgba(255,255,255,0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; padding: 36px 24px; position: relative; transition: var(--transition); }
        .step-card:hover { background: rgba(255,255,255,0.2); transform: translateY(-6px); }
        .step-number { width: 48px; height: 48px; background: var(--accent-color); color: var(--text-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; margin: 0 auto 20px; }
        .step-card i { font-size: 30px; color: var(--white); margin-bottom: 16px; display: block; }
        .step-card h4 { font-size: 17px; font-weight: 600; margin-bottom: 10px; }
        .step-card p { font-size: 14px; color: rgba(255,255,255,0.8); line-height: 1.6; }
        .step-connector { display: none; }
        @media (min-width: 900px) {
            .steps-grid { grid-template-columns: repeat(4, 1fr); }
        }

        /* ── FAQ ── */
        .faq { padding: 80px 20px; background: var(--bg-light); }
        .faq .section-title, .faq .section-subtitle { text-align: center; }
        .faq-list { max-width: 780px; margin: 0 auto; display: flex; flex-direction: column; gap: 14px; }
        .faq-item { background: var(--white); border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid rgba(0,74,173,0.08); }
        .faq-question { width: 100%; background: none; border: none; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; text-align: left; font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 600; color: var(--text-dark); transition: var(--transition); }
        .faq-question:hover { color: var(--primary-color); }
        .faq-question .faq-icon { width: 30px; height: 30px; min-width: 30px; border-radius: 50%; background: rgba(0,74,173,0.08); color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 13px; transition: var(--transition); }
        .faq-item.open .faq-question .faq-icon { background: var(--primary-color); color: var(--white); transform: rotate(45deg); }
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.35s ease, padding 0.35s ease; }
        .faq-answer p { padding: 0 24px 20px; font-size: 14px; color: var(--text-light); line-height: 1.7; }
        .faq-item.open .faq-answer { max-height: 200px; }

        @media (max-width: 768px) {
            .agencies, .how-it-works, .faq { padding: 60px 20px; }
            .step-card { padding: 28px 20px; }
        }

        /* ── Live Animated Circles ── */
        .hero-circles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
        .hero-circles span {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: floatCircle linear infinite;
        }
        .hero-circles span:nth-child(1)  { width:320px; height:320px; top:-80px;  left:-80px;  animation-duration:18s; }
        .hero-circles span:nth-child(2)  { width:180px; height:180px; top:60px;   right:10%;   animation-duration:12s; animation-delay:-4s; background:rgba(255,255,255,0.06); }
        .hero-circles span:nth-child(3)  { width:100px; height:100px; bottom:30px; left:15%;   animation-duration:9s;  animation-delay:-2s; background:rgba(255,215,0,0.12); }
        .hero-circles span:nth-child(4)  { width:220px; height:220px; bottom:-60px; right:-40px; animation-duration:15s; animation-delay:-7s; }
        .hero-circles span:nth-child(5)  { width:60px;  height:60px;  top:40%;  left:5%;     animation-duration:7s;  animation-delay:-1s; background:rgba(255,215,0,0.1); }
        .hero-circles span:nth-child(6)  { width:140px; height:140px; top:20%;  right:25%;   animation-duration:11s; animation-delay:-5s; background:rgba(255,255,255,0.05); }
        @keyframes floatCircle {
            0%   { transform: translateY(0)     scale(1);    opacity:.7; }
            33%  { transform: translateY(-22px) scale(1.04); opacity:1;  }
            66%  { transform: translateY(12px)  scale(.97);  opacity:.8; }
            100% { transform: translateY(0)     scale(1);    opacity:.7; }
        }

        /* ── Live pulse dot badge ── */
        .live-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.3); border-radius: 50px;
            padding: 7px 18px; font-size: 12.5px; font-weight: 500;
            margin-bottom: 22px; letter-spacing: 0.3px; position: relative; z-index: 1;
        }
        .live-dot {
            width: 9px; height: 9px; border-radius: 50%;
            background: #4ade80; box-shadow: 0 0 0 0 rgba(74,222,128,0.6);
            animation: livePulse 1.8s ease-out infinite;
        }
        @keyframes livePulse {
            0%   { box-shadow: 0 0 0 0   rgba(74,222,128,0.7); }
            70%  { box-shadow: 0 0 0 9px rgba(74,222,128,0);   }
            100% { box-shadow: 0 0 0 0   rgba(74,222,128,0);   }
        }

        /* ensure hero content sits above circles */
        header > *:not(.hero-circles) { position: relative; z-index: 1; }

        /* ── Stats bar ── */
        .hero-stats {
            display: flex; align-items: center; justify-content: center;
            margin-top: 50px;
            background: rgba(255,255,255,0.13); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.22); border-radius: 16px;
            padding: 18px 36px; max-width: 400px;
            margin-left: auto; margin-right: auto;
        }
        .hero-stat  { display: flex; flex-direction: column; align-items: center; flex: 1; }
        .stat-num   { font-size: 24px; font-weight: 700; color: var(--accent-color); line-height: 1.1; }
        .stat-label { font-size: 11.5px; opacity: .78; margin-top: 2px; font-weight: 400; letter-spacing: .4px; }
        .stat-divider { width: 1px; height: 36px; background: rgba(255,255,255,0.22); margin: 0 16px; }

        @media (max-width: 480px) {
            .hero-stats { padding: 14px 20px; max-width: 300px; }
            .stat-num { font-size: 20px; }
            .live-badge { font-size: 11.5px; }
        }

        @media (max-width: 900px) { .nav-center { display: none; } .hamburger { display: flex; } .user-profile { display: none; } }
        @media (max-width: 600px) { nav { padding: 14px 4%; } .nav-left h2 { font-size: 18px; } .nav-right .login-btn { display: none; } }

    </style>
</head>
<body>

    <nav>
        <div class="nav-left">
            <h2><i class="fa-solid fa-building-columns"></i> E-Gov Portal</h2>
        </div>
        <ul class="nav-center">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="service.php">Services</a></li>
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
                <li><a href="index.php" class="active"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="service.php"><i class="fa-solid fa-list-check"></i> Services</a></li>
                <li><a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Online Schedule</a></li>
                <li><a href="about.php"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
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

    <header id="home">
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

        <h1>Design and Implementation of an E-Government Service Portal with Smart Accessibility Tools for Mobility-Impaired Users</h1>
        <p>A secure, accessible, and user-friendly government portal designed specifically to assist citizens with mobility limitations.</p>
        <div class="hero-buttons">
            <a href="service.php" class="btn btn-primary">Apply Now <i class="fa-solid fa-pen-to-square"></i></a>
            <a href="about.php" class="btn btn-outline">Learn More</a>
        </div>

    </header>

    <section class="features">
        <h2 class="section-title">Why Choose Our Portal?</h2>
        <p class="section-subtitle">We are committed to breaking down barriers and ensuring equal access to essential government services.</p>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon-box"><i class="fa-solid fa-laptop-medical"></i></div>
                <h3>Easy Online Access</h3>
                <p>Access government services anytime, anywhere, without the need to physically visit crowded offices.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><i class="fa-solid fa-universal-access"></i></div>
                <h3>Accessibility Tools</h3>
                <p>Built-in high contrast mode, screen reader compatibility, and adjustable text sizes for all users.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><i class="fa-solid fa-bolt"></i></div>
                <h3>Fast Processing</h3>
                <p>Submit applications and track requests digitally with real-time status updates.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><i class="fa-solid fa-hands-holding-child"></i></div>
                <h3>Inclusive System</h3>
                <p>Designed with empathy for persons with mobility limitations, ensuring a seamless experience.</p>
            </div>
        </div>
    </section>

    <!-- ── How It Works ── -->
    <section class="how-it-works">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Apply for government services in just four simple steps — no office visit required.</p>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <i class="fa-solid fa-user-plus"></i>
                <h4>Create an Account</h4>
                <p>Register and verify your identity to gain secure access to all government services on the portal.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <i class="fa-solid fa-list-check"></i>
                <h4>Choose a Service</h4>
                <p>Browse available agencies and select the service or assistance program you wish to apply for.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <i class="fa-solid fa-file-pen"></i>
                <h4>Submit Application</h4>
                <p>Fill out the online form, upload the required documents, and submit your application digitally.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <i class="fa-solid fa-bell"></i>
                <h4>Track & Receive</h4>
                <p>Monitor your application status in real time and receive notifications on updates and approvals.</p>
            </div>
        </div>
    </section>

    <!-- ── FAQ ── -->
    <section class="faq">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <p class="section-subtitle" style="text-align:center; color:var(--text-light); margin-bottom:40px; max-width:600px; margin-left:auto; margin-right:auto;">Have questions? We've got answers. If you need further help, feel free to contact us.</p>
        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Who can use this portal?
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div class="faq-answer"><p>Any Filipino citizen can use this portal, with special accessibility features designed for persons with mobility impairments to ensure a seamless and inclusive experience.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Is it free to apply through the portal?
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div class="faq-answer"><p>Yes. Submitting applications through the portal is completely free of charge. Government service fees, if applicable, are handled separately by the respective agency.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    How do I track my application status?
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div class="faq-answer"><p>After logging in, go to <strong>Track Application</strong> from your profile menu. You can view the current status of all your submitted applications in real time.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    What documents are required for applications?
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div class="faq-answer"><p>Required documents vary per service and agency. Each application flow will clearly list what you need to prepare and upload before submitting your form.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Is my personal data secure?
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div class="faq-answer"><p>Yes. The portal uses secure session management and follows government data privacy standards under the Philippine Data Privacy Act (RA 10173) to protect your information.</p></div>
            </div>
        </div>
    </section>

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
    </script>

    <script>
    // FAQ accordion
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => {
                i.classList.remove('open');
                i.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
            });
            if (!isOpen) {
                item.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });
    </script>
</body>
</html>