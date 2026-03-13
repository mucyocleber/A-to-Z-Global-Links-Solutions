<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user profile image if logged in
$user_profile_image = null;
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'config/database.php';
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user && $user['profile_image']) {
            $user_profile_image = $user['profile_image'];
        }
    } catch (Exception $e) {
        // Continue without profile image
    }
}

// Language handling
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
$_SESSION['lang'] = $lang;

// Language translations
$translations = [
    'en' => [
        'home' => 'Home',
        'about' => 'About', 
        'services' => 'Services',
        'application' => 'Application',
        'contact' => 'Contact',
        'get_started' => 'Get Started',
        'register' => 'Register'
    ],
    'fr' => [
        'home' => 'Accueil',
        'about' => 'À propos',
        'services' => 'Services', 
        'application' => 'Candidature',
        'contact' => 'Contact',
        'get_started' => 'Commencer',
        'login' => 'Connexion',
        'register' => 'S\'inscrire'
    ],
    'rw' => [
        'home' => 'Ahabanza',
        'about' => 'Ibibazo',
        'services' => 'Serivisi',
        'application' => 'Gusaba',
        'contact' => 'Twandikire',
        'get_started' => 'Tangira',
        'login' => 'Injira',
        'register' => 'Iyandikishe'
    ]
];

$t = $translations[$lang] ?? $translations['en'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="logo/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="logo/logo.jpg">
    <title><?= $pageTitle ?? 'A to Z Global Link Solutions - Professional Visa & Immigration Services' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; padding-bottom: 80px; }
        
        /* Preloader */
        .preloader { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.5s ease-out; }
        .preloader.fade-out { opacity: 0; pointer-events: none; }
        .preloader-logo { width: 120px; height: 120px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); animation: pulse 2s infinite; }
        .preloader-logo img { width: 80px; height: 80px; object-fit: contain; }
        .preloader-spinner { width: 60px; height: 60px; border: 4px solid rgba(255, 255, 255, 0.3); border-top: 4px solid white; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1rem; }
        .preloader-text { color: white; font-size: 1.2rem; font-weight: 600; text-align: center; }
        .preloader-dots { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .preloader-dot { width: 8px; height: 8px; background: rgba(255, 255, 255, 0.7); border-radius: 50%; animation: bounce 1.4s infinite ease-in-out; }
        .preloader-dot:nth-child(1) { animation-delay: -0.32s; }
        .preloader-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        @keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
        
        .header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(30, 64, 175, 0.3); }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 3%; max-width: 1400px; margin: 0 auto; height: 70px; }
        
        /* Logo Section */
        .logo { display: flex; align-items: center; gap: 0.75rem; font-size: 1.4rem; font-weight: 700; color: white; text-decoration: none; flex-shrink: 0; }
        .logo img { height: 45px; width: 45px; border-radius: 50%; background: white; padding: 6px; object-fit: contain; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
        
        /* Navigation Links */
        .nav-center { display: flex; align-items: center; }
        .nav-links { display: flex; list-style: none; gap: 1.2rem; margin: 0; }
        .nav-links a { text-decoration: none; color: rgba(255, 255, 255, 0.9); font-weight: 500; transition: all 0.3s; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.95rem; }
        .nav-links a:hover { color: white; background: rgba(255, 255, 255, 0.1); }
        
        /* Right Section */
        .nav-right { display: flex; align-items: center; gap: 1rem; flex-shrink: 0; }
        
        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            backdrop-filter: blur(8px);
        }
        
        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .mobile-menu {
            position: fixed;
            bottom: 0;
            left: 50%;
            width: 90%;
            max-width: 400px;
            background: white;
            border-radius: 24px 24px 0 0;
            transform: translateX(-50%) translateY(100%) scale(0.9);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-height: 60vh;
            overflow: hidden;
            box-shadow: 0 -20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .mobile-menu-overlay.active .mobile-menu {
            transform: translateX(-50%) translateY(0) scale(1);
        }
        
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
        }
        
        .mobile-menu-header h3 {
            margin: 0;
            color: #1e40af;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .close-menu {
            background: #f1f5f9;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .close-menu:hover {
            background: #e2e8f0;
            color: #1e40af;
            transform: rotate(90deg);
        }
        .mobile-menu-content {
            padding: 0.5rem 0 2rem;
        }
        
        .mobile-menu-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: #374151;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }
        
        .mobile-menu-link:hover {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.1), transparent);
            border-left-color: #3b82f6;
            color: #1e40af;
            transform: translateX(8px);
        }
        
        .mobile-menu-link i {
            width: 20px;
            color: #3b82f6;
            font-size: 1.1rem;
        }
        
        /* User Profile Dropdown */
        .user-profile {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .user-profile:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 0;
        }
        
        .user-name {
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
        }
        
        .user-role {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .dropdown-arrow {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
            transition: transform 0.3s ease;
            margin-left: 0.25rem;
        }
        
        .user-profile.active .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1001;
            border: 1px solid rgba(59, 130, 246, 0.1);
            overflow: hidden;
        }
        
        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }
        
        .dropdown-header {
            padding: 1.25rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #f1f5f9;
        }
        
        .dropdown-user-info {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }
        
        .dropdown-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            overflow: hidden;
        }
        
        .dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .dropdown-user-details {
            flex: 1;
            min-width: 0;
        }
        
        .dropdown-user-details h4 {
            color: #1e293b;
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .dropdown-user-details p {
            color: #64748b;
            font-size: 0.8rem;
            margin: 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .dropdown-menu {
            padding: 0.5rem 0;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1.25rem;
            color: #374151;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.08), transparent);
            color: #1e40af;
            transform: translateX(4px);
        }
        
        .dropdown-item i {
            width: 18px;
            color: #3b82f6;
            font-size: 1rem;
            text-align: center;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 0.5rem 0;
        }
        
        /* CTA Buttons */
        .nav-cta { display: flex; gap: 0.6rem; }
        .btn-get-started { background: rgba(255, 255, 255, 0.15); color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 20px; font-weight: 600; transition: all 0.3s; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); font-size: 0.85rem; }
        .btn-get-started:hover { background: rgba(255, 255, 255, 0.25); transform: translateY(-1px); }
        .btn-login { background: white; color: #1e40af; padding: 0.5rem 1rem; text-decoration: none; border-radius: 20px; font-weight: 600; transition: all 0.3s; font-size: 0.85rem; }
        .btn-login:hover { background: #f8fafc; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        
        /* Mobile Bottom Navigation */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(59, 130, 246, 0.2);
            padding: 0.5rem 0;
            z-index: 1000;
            box-shadow: 0 -8px 32px rgba(30, 64, 175, 0.15);
        }
        
        .mobile-nav-links {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 500px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.75rem 0.5rem;
            border-radius: 12px;
            min-width: 60px;
            position: relative;
        }
        
        .mobile-nav-item:hover,
        .mobile-nav-item.active {
            color: #1e40af;
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }
        
        .mobile-nav-item i {
            font-size: 1.4rem;
            margin-bottom: 0.3rem;
            transition: all 0.3s ease;
        }
        
        .mobile-nav-item:hover i {
            transform: scale(1.1);
        }
        
        .mobile-nav-item.special {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            padding: 0;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            animation: pulse-glow 2s infinite;
            position: relative;
            overflow: hidden;
        }
        
        .mobile-nav-item.special::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 8px 30px rgba(59, 130, 246, 0.6), 0 0 20px rgba(59, 130, 246, 0.3);
            }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .mobile-nav-item.special:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-3px) scale(1.05);
            color: white;
        }
        
        .mobile-nav-item.special i {
            font-size: 1.6rem;
            margin-bottom: 0;
        }
        
        .mobile-nav-item.special span {
            display: none;
        }
        
        .mobile-nav-item.profile {
            position: relative;
        }
        
        .mobile-nav-item.profile::before {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        @media (max-width: 1200px) {
            .nav { padding: 0.75rem 2%; }
            .nav-links { gap: 1rem; }
            .nav-links a { padding: 0.3rem 0.6rem; font-size: 0.9rem; }
        }
        
        @media (max-width: 992px) {
            .logo { font-size: 1.2rem; }
            .logo img { height: 40px; width: 40px; }
            .nav-links { gap: 0.8rem; }
            .nav-right { gap: 0.8rem; }
        }
        
        @media (max-width: 768px) {
            .nav-links, .nav-cta { display: none; }
            .mobile-nav { display: block; }
            .logo { font-size: 0.9rem; gap: 0.5rem; }
            .logo img { height: 30px; width: 30px; }
            .nav { padding: 0.5rem 2%; height: 60px; }
            .lang-dropdown { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
            .lang-menu { min-width: 120px; }
            .user-profile { display: none; }
        }
        
        @media (max-width: 480px) {
            .logo { font-size: 0.8rem; }
            .logo img { height: 28px; width: 28px; }
            .nav { padding: 0.5rem 1.5%; }
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="preloader-logo">
            <img src="logo/logo.jpg" alt="A to Z Global Link">
        </div>
        <div class="preloader-spinner"></div>
        <div class="preloader-text">Loading Your Gateway to Global Opportunities</div>
        <div class="preloader-dots">
            <div class="preloader-dot"></div>
            <div class="preloader-dot"></div>
            <div class="preloader-dot"></div>
        </div>
    </div>
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">
                <img src="logo/logo.jpg" alt="A to Z Global Link">
                A to Z Global Link Solutions
            </a>
            <div class="nav-center">
                <ul class="nav-links">
                    <li><a href="index.php"><?= $t['home'] ?></a></li>
                    <li><a href="about.php"><?= $t['about'] ?></a></li>
                    <li><a href="services.php"><?= $t['services'] ?></a></li>
                    <li><a href="application.php"><?= $t['application'] ?></a></li>
                    <li><a href="contact.php"><?= $t['contact'] ?></a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </div>
            <div class="nav-right">
                <div class="nav-cta">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-profile" onclick="toggleProfileDropdown()">
                            <div class="user-avatar">
                                <?php if ($user_profile_image): ?>
                                    <img src="<?= htmlspecialchars($user_profile_image) ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?= explode(' ', $_SESSION['user_name'])[0] ?></div>
                                <div class="user-role">Member</div>
                            </div>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                            <div class="profile-dropdown" id="profileDropdown">
                                <div class="dropdown-header">
                                    <div class="dropdown-user-info">
                                        <div class="dropdown-avatar">
                                            <?php if ($user_profile_image): ?>
                                                <img src="<?= htmlspecialchars($user_profile_image) ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dropdown-user-details">
                                            <h4><?= $_SESSION['user_name'] ?></h4>
                                            <p>Active Member</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-menu">
                                    <a href="profile.php" class="dropdown-item">
                                        <i class="fas fa-user"></i>
                                        <span>My Profile</span>
                                    </a>
                                    <a href="application.php" class="dropdown-item">
                                        <i class="fas fa-file-alt"></i>
                                        <span>Applications</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php 
                        $current_page = basename($_SERVER['PHP_SELF']);
                        if ($current_page === 'login.php'): ?>
                            <a href="register.php" class="btn-get-started">Register</a>
                            <a href="index.php" class="btn-login">Home</a>
                        <?php elseif ($current_page === 'register.php'): ?>
                            <a href="login.php" class="btn-get-started">Login</a>
                            <a href="index.php" class="btn-login">Home</a>
                        <?php else: ?>
                            <a href="register.php" class="btn-get-started"><?= $t['get_started'] ?></a>
                            <a href="login.php" class="btn-login">Login</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-nav">
        <div class="mobile-nav-links">
            <a href="index.php" class="mobile-nav-item">
                <i class="fas fa-home"></i>
                <span><?= $t['home'] ?></span>
            </a>
            
            <a href="services.php" class="mobile-nav-item">
                <i class="fas fa-concierge-bell"></i>
                <span><?= $t['services'] ?></span>
            </a>
            
            <a href="application.php" class="mobile-nav-item special">
                <i class="fas fa-plus"></i>
                <span>Apply</span>
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="mobile-nav-item profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
                
                <div class="mobile-nav-item menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-ellipsis-h"></i>
                    <span>More</span>
                </div>
            <?php else: ?>
                <a href="login.php" class="mobile-nav-item">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
                
                <div class="mobile-nav-item menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                    <span>Menu</span>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()">
        <div class="mobile-menu" onclick="event.stopPropagation()">
            <div class="mobile-menu-header">
                <h3>Menu</h3>
                <button class="close-menu" onclick="closeMobileMenu()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-menu-content">
                <a href="about.php" class="mobile-menu-link">
                    <i class="fas fa-info-circle"></i>
                    <span><?= $t['about'] ?></span>
                </a>
                <a href="contact.php" class="mobile-menu-link">
                    <i class="fas fa-envelope"></i>
                    <span><?= $t['contact'] ?></span>
                </a>
                <a href="faq.php" class="mobile-menu-link">
                    <i class="fas fa-question-circle"></i>
                    <span>FAQ</span>
                </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="mobile-menu-link">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    <?php else: ?>
                        <a href="logout.php" class="mobile-menu-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Preloader
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('preloader').classList.add('fade-out');
                setTimeout(() => {
                    document.getElementById('preloader').style.display = 'none';
                }, 500);
            }, 1500);
        });
        
        // Profile dropdown functions
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            const userProfile = document.querySelector('.user-profile');
            
            dropdown.classList.toggle('show');
            userProfile.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userProfile = document.querySelector('.user-profile');
            const dropdown = document.getElementById('profileDropdown');
            
            if (userProfile && !userProfile.contains(event.target)) {
                dropdown.classList.remove('show');
                userProfile.classList.remove('active');
            }
        });
        
        // Mobile menu functions
        function toggleMobileMenu() {
            document.getElementById('mobileMenuOverlay').classList.add('active');
        }
        
        function closeMobileMenu() {
            document.getElementById('mobileMenuOverlay').classList.remove('active');
        }
    </script>