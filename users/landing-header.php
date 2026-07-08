<?php
// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camiguin Rentals</title>

    <!-- Favicon -->
    <link rel="icon" href="/camiguin-rental/image/easeico.ico" type="image/x-icon">

    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="/camiguin-rental/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/camiguin-rental/assets/fontawesome/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* ============================================
           NAVBAR BASE STYLING
           ============================================ */
        .main-navbar {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .navbar-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        /* ============================================
           LOGO STYLING
           ============================================ */
        .navbar-brand {
            display: flex;
            align-items: center;
            padding: 0;
            margin: 0;
            transition: transform 0.3s ease;
            text-decoration: none;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-logo {
            height: 70px;
            width: auto;
            transition: height 0.3s ease;
        }

        /* ============================================
           NAVIGATION LINKS
           ============================================ */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-link-item {
            color: #495057;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .nav-link-item:hover {
            background: #f8f9fa;
            color: #007bff;
            transform: translateY(-2px);
        }

        .nav-link-item i {
            font-size: 14px;
            color: #6c757d;
            transition: color 0.25s ease;
        }

        .nav-link-item:hover i {
            color: #007bff;
        }

        /* ============================================
           BUTTONS
           ============================================ */
        .btn-explore {
            background: transparent;
            border: 2px solid #6c757d;
            color: #495057;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-explore:hover {
            background: #6c757d;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .btn-login {
            background: #69a5e5ff;
            border: 2px solid #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-login:hover {
            background: #0056b3;
            border-color: #0056b3;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
        }

        /* ============================================
           MOBILE MENU TOGGLE
           ============================================ */
        .mobile-toggle {
            display: none;
            background: transparent;
            border: 2px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-toggle:hover {
            background: #f8f9fa;
            border-color: #007bff;
        }

        .mobile-toggle i {
            font-size: 1.5rem;
            color: #495057;
            transition: transform 0.3s ease;
        }

        .mobile-toggle.active i {
            transform: rotate(90deg);
        }

        /* ============================================
           MOBILE MENU
           ============================================ */
        .mobile-menu {
            display: none;
            background: #ffffff;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 0.75rem;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.4s ease, opacity 0.3s ease;
            opacity: 0;
        }

        .mobile-menu.active {
            max-height: 500px;
            opacity: 1;
        }

        .mobile-menu-list {
            list-style: none;
            padding: 1rem;
            margin: 0;
        }

        .mobile-menu-item {
            margin-bottom: 0.5rem;
        }

        .mobile-menu-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #495057;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.25s ease;
            font-weight: 500;
        }

        .mobile-menu-link:hover {
            background: #f8f9fa;
            color: #007bff;
        }

        .mobile-menu-link i {
            font-size: 16px;
            color: #6c757d;
            width: 20px;
        }

        .mobile-menu-link:hover i {
            color: #007bff;
        }

        /* Mobile buttons */
        .mobile-btn-explore,
        .mobile-btn-login {
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
        }

        .mobile-btn-explore {
            background: transparent;
            border: 2px solid #6c757d;
            color: #495057;
        }

        .mobile-btn-explore:hover {
            background: #6c757d;
            color: #fff;
        }

        .mobile-btn-login {
            background: #007bff;
            border: 2px solid #007bff;
            color: #fff;
        }

        .mobile-btn-login:hover {
            background: #0056b3;
            border-color: #0056b3;
        }

        /* ============================================
           RESPONSIVE - DESKTOP (>1200px)
           ============================================ */
        @media (min-width: 1201px) {
            .navbar-logo {
                height: 75px;
            }

            .nav-links {
                gap: 0.75rem;
            }

            .nav-link-item {
                padding: 10px 18px;
                font-size: 15px;
            }
        }

        /* ============================================
           RESPONSIVE - LAPTOP (992px - 1200px)
           ============================================ */
        @media (min-width: 992px) and (max-width: 1200px) {
            .navbar-logo {
                height: 65px;
            }

            .nav-links {
                gap: 0.5rem;
            }

            .nav-link-item {
                padding: 8px 14px;
                font-size: 14px;
            }

            .btn-explore,
            .btn-login {
                padding: 8px 16px;
                font-size: 14px;
            }
        }

        /* ============================================
           RESPONSIVE - TABLET (768px - 991px)
           ============================================ */
        @media (min-width: 768px) and (max-width: 991px) {
            .navbar-logo {
                height: 60px;
            }

            .nav-links {
                display: none;
            }

            .mobile-toggle {
                display: flex;
            }

            .mobile-menu {
                display: block;
            }
        }

        /* ============================================
           RESPONSIVE - MOBILE (< 768px)
           ============================================ */
        @media (max-width: 767px) {
            .main-navbar {
                padding: 0.5rem 0;
            }

            .navbar-container {
                padding: 0 1rem;
            }

            .navbar-logo {
                height: 55px;
            }

            .nav-links {
                display: none;
            }

            .mobile-toggle {
                display: flex;
            }

            .mobile-menu {
                display: block;
            }

            .mobile-menu-list {
                padding: 0.75rem;
            }

            .mobile-menu-link {
                padding: 10px 14px;
                font-size: 14px;
            }
        }

        /* ============================================
           RESPONSIVE - SMALL MOBILE (< 480px)
           ============================================ */
        @media (max-width: 479px) {
            .navbar-logo {
                height: 50px;
            }

            .navbar-container {
                padding: 0 0.75rem;
            }

            .mobile-toggle {
                padding: 6px 10px;
            }

            .mobile-toggle i {
                font-size: 1.25rem;
            }

            .mobile-menu-link {
                padding: 10px 12px;
                font-size: 13px;
            }

            .mobile-menu-link i {
                font-size: 14px;
            }
        }

        /* ============================================
           LANDSCAPE MODE
           ============================================ */
        @media (max-height: 600px) and (orientation: landscape) {
            .navbar-logo {
                height: 45px;
            }

            .main-navbar {
                padding: 0.4rem 0;
            }

            .mobile-menu.active {
                max-height: 300px;
                overflow-y: auto;
            }
        }

        /* ============================================
           SMOOTH SCROLLING
           ============================================ */
        html {
            scroll-behavior: smooth;
        }

        /* ============================================
           ACCESSIBILITY
           ============================================ */
        *:focus-visible {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }

        /* Custom scrollbar for mobile menu */
        .mobile-menu::-webkit-scrollbar {
            width: 4px;
        }

        .mobile-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .mobile-menu::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .mobile-menu::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Prevent body scroll on iOS when menu is open */
        body.menu-open {
            position: fixed;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Landing Page Header -->
    <nav class="main-navbar">
        <div class="navbar-container">
            <div class="navbar-wrapper">
                <!-- Logo -->
                <a class="navbar-brand" href="/camiguin-rental/landing-page.php">
                    <img src="/camiguin-rental/image/logo.png" alt="Camiguin Rentals" class="navbar-logo">
                </a>

                <!-- Desktop Navigation Links (Hidden on mobile/tablet) -->
                <ul class="nav-links">
                    <li><a class="nav-link-item" href="/camiguin-rental/landing-page.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a></li>
                    <li><a class="nav-link-item" href="/camiguin-rental/landing-page.php#about">
                        <i class="fas fa-info-circle"></i>
                        <span>About Us</span>
                    </a></li>
                    <li><a class="nav-link-item" href="/camiguin-rental/landing-page.php#faqs">
                        <i class="fas fa-question-circle"></i>
                        <span>FAQs</span>
                    </a></li>
                    <li><a class="btn-explore" href="/camiguin-rental/users/landing-categories.php">
                        <i class="fas fa-compass"></i>
                        <span>Explore More</span>
                    </a></li>
                    <li><a class="nav-link-item" href="/camiguin-rental/users/landing-message.php#contact">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Us</span>
                    </a></li>
                    <li><a class="btn-login" href="/camiguin-rental/users/user-login.php">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login / SignUp</span>
                    </a></li>
                </ul>

                <!-- Mobile Toggle Button (Hidden on desktop) -->
                <button id="mobileToggle" class="mobile-toggle" aria-label="Toggle navigation" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Mobile Menu (Dropdown below navbar) -->
            <div id="mobileMenu" class="mobile-menu">
                <ul class="mobile-menu-list">
                    <li class="mobile-menu-item">
                        <a class="mobile-menu-link" href="/camiguin-rental/landing-page.php#home">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="mobile-menu-item">
                        <a class="mobile-menu-link" href="/camiguin-rental/landing-page.php#about">
                            <i class="fas fa-info-circle"></i>
                            <span>About Us</span>
                        </a>
                    </li>
                    <li class="mobile-menu-item">
                        <a class="mobile-menu-link" href="/camiguin-rental/landing-page.php#faqs">
                            <i class="fas fa-question-circle"></i>
                            <span>FAQs</span>
                        </a>
                    </li>
                    <li class="mobile-menu-item">
                        <a class="mobile-menu-link mobile-btn-explore btn-explore" href="/camiguin-rental/users/landing-categories.php">
                            <i class="fas fa-compass"></i>
                            <span>Explore More</span>
                        </a>
                    </li>
                    <li class="mobile-menu-item">
                        <a class="mobile-menu-link" href="/camiguin-rental/users/landing-message.php#contact">
                            <i class="fas fa-envelope"></i>
                            <span>Contact Us</span>
                        </a>
                    </li>
                    <li class="mobile-menu-item">
                        <a class="mobile-menu-link mobile-btn-login btn-login" href="/camiguin-rental/users/user-login.php">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login / SignUp</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="/camiguin-rental/assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        (function() {
            'use strict';

            const mobileToggle = document.getElementById('mobileToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            const body = document.body;

            function toggleMobileMenu() {
                const isActive = mobileMenu.classList.contains('active');
                if (isActive) {
                    closeMobileMenu();
                } else {
                    openMobileMenu();
                }
            }

            function openMobileMenu() {
                mobileMenu.classList.add('active');
                mobileToggle.classList.add('active');
                mobileToggle.setAttribute('aria-expanded', 'true');
                body.classList.add('menu-open');
            }

            function closeMobileMenu() {
                mobileMenu.classList.remove('active');
                mobileToggle.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
                body.classList.remove('menu-open');
            }

            if (mobileToggle) {
                mobileToggle.addEventListener('click', toggleMobileMenu);
            }

            const mobileMenuLinks = mobileMenu.querySelectorAll('.mobile-menu-link');
            mobileMenuLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    setTimeout(closeMobileMenu, 200);
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                    closeMobileMenu();
                }
            });

            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth >= 992) {
                        closeMobileMenu();
                    }
                }, 250);
            });

            document.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    if (!mobileToggle.contains(e.target) &&
                        !mobileMenu.contains(e.target) &&
                        mobileMenu.classList.contains('active')) {
                        closeMobileMenu();
                    }
                }
            });

            mobileToggle.setAttribute('aria-expanded', 'false');

        })();
    </script>
</body>
</html>