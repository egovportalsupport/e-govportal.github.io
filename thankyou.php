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
    <title>Thank You - E-Government Service Portal</title>
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
body { background-color: var(--bg-light); color: var(--text-dark); line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }

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
.nav-right .login-btn:hover { background: var(--secondary-color); transform: translateY(-2px); }

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
.drawer-footer { padding: 20px 24px; border-top: 1px solid #f0f0f0; }
.drawer-footer .login-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 13px 20px; background: var(--primary-color); color: var(--white); text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 14px; transition: var(--transition); }
.drawer-footer .login-btn:hover { background: var(--secondary-color); }

@media (max-width: 900px) { .nav-center { display: none; } .hamburger { display: flex; } .user-profile { display: none; } }
@media (max-width: 600px) { nav { padding: 14px 4%; } .nav-left h2 { font-size: 18px; } .nav-right .login-btn { display: none; } }

/* ── Thank You Page ── */
.thankyou-section {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
}

.thankyou-card {
    background: var(--white);
    border-radius: 24px;
    box-shadow: var(--shadow);
    max-width: 560px;
    width: 100%;
    text-align: center;
    padding: 56px 44px 48px;
    animation: fadeUp 0.5s ease both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}

.check-circle {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 28px;
    font-size: 40px;
    color: #2e7d32;
    animation: popIn 0.5s 0.2s cubic-bezier(0.34,1.56,0.64,1) both;
}

@keyframes popIn {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

.thankyou-card h1 {
    font-size: 30px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 14px;
}

.thankyou-card .subtitle {
    font-size: 15px;
    color: var(--text-light);
    line-height: 1.7;
    margin-bottom: 32px;
}

.thankyou-card .subtitle strong {
    color: var(--text-dark);
}

.info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f0f5ff;
    border-radius: 10px;
    padding: 12px 18px;
    margin-bottom: 10px;
    font-size: 14px;
    color: var(--text-dark);
    text-align: left;
}

.info-row i {
    color: var(--primary);
    width: 18px;
    text-align: center;
    flex-shrink: 0;
}

.btn-group {
    display: flex;
    gap: 14px;
    margin-top: 32px;
    flex-wrap: wrap;
}

.btn-primary {
    flex: 1;
    padding: 13px 20px;
    background: var(--primary);
    color: var(--white);
    border: none;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
    box-shadow: 0 4px 12px rgba(0,74,173,0.2);
}

.btn-primary:hover { background: var(--secondary); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,119,255,0.3); }

.btn-outline {
    flex: 1;
    padding: 13px 20px;
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
}

.btn-outline:hover { background: #f0f5ff; }

footer { background: #002a66; color: white; text-align: center; padding: 30px 20px; }

@media (max-width: 480px) {
    .thankyou-card { padding: 40px 24px 36px; }
    .thankyou-card h1 { font-size: 24px; }
    .btn-group { flex-direction: column; }
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
                <li><a href="about.php"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
                <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> Contact Us</a></li>
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

    <section class="thankyou-section">
        <div class="thankyou-card">
            <div class="check-circle">
                <i class="fa-solid fa-check"></i>
            </div>
            <h1>Thank You!</h1>
            <p class="subtitle">
                Your message has been successfully sent, <strong id="senderName">there</strong>.<br>
                Our team will review your concern and respond within <strong>24 hours</strong>.
            </p>

            <div class="info-row">
                <i class="fa-solid fa-clock"></i>
                Expected response time: <strong>within 24 hours</strong>
            </div>
            <div class="info-row">
                <i class="fa-solid fa-envelope"></i>
                A confirmation will be sent to your email address.
            </div>
            <div class="info-row">
                <i class="fa-solid fa-headset"></i>
                For urgent concerns, use our 24/7 portal chatbot.
            </div>

            <div class="btn-group">
                <a href="index.php" class="btn-primary">
                    <i class="fa-solid fa-house"></i> Back to Home
                </a>
                <a href="contact.php" class="btn-outline">
                    <i class="fa-solid fa-envelope"></i> Send Another
                </a>
            </div>
        </div>
    </section>

    <footer>
        <p>Accessible Government Digital System &copy; 2026</p>
    </footer>

    <script src="navbar.js"></script>
    <script>
    // Show sender's first name if available from sessionStorage
    const name = sessionStorage.getItem('contactName');
    if (name) {
        document.getElementById('senderName').textContent = name;
        sessionStorage.removeItem('contactName');
    }

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
    <?php include __DIR__ . '/accessibility_widget.php'; ?>
</body>
</html>