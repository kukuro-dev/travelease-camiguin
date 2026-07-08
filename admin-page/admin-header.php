<?php
// Start session only if none exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch admin info from session
$fullname       = $_SESSION['fullname'] ?? '';
$email          = $_SESSION['email'] ?? '';
$contact_number = $_SESSION['contact_number'] ?? '';
$username       = $_SESSION['username'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Camiguin Rentals</title>
<link rel="icon" href="../image/easeico.ico" type="image/x-icon">

<!-- Bootstrap & FontAwesome -->
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">

<style>
/* Professional Admin Navigation */
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f8f9fa;
    padding-top: 70px;
}

.admin-navbar {
    background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
    box-shadow: 0 2px 15px rgba(0,0,0,0.15);
    padding: 0;
    min-height: 70px;
}

/* Brand Section */
.navbar-brand-admin {
    display: flex;
    align-items: center;
    padding: 12px 0;
    color: #ffffff;
    font-weight: 600;
    font-size: 19px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.navbar-brand-admin:hover {
    color: #ecf0f1;
    text-decoration: none;
    transform: translateX(3px);
}

.navbar-brand-admin img {
    height: 45px;
    margin-right: 12px;
    transition: transform 0.3s ease;
    filter: brightness(1.1);
}

.navbar-brand-admin:hover img {
    transform: rotate(-5deg) scale(1.05);
}

/* Admin Badge */
.admin-badge {
    display: inline-block;
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid #ffc107;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-left: 10px;
    vertical-align: middle;
}

/* Navigation Links */
.navbar-nav-admin {
    gap: 3px;
}

.navbar-nav-admin .nav-link {
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
    font-size: 14px;
    padding: 12px 18px;
    border-radius: 4px;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
    position: relative;
}

.navbar-nav-admin .nav-link i {
    margin-right: 7px;
    font-size: 14px;
}

.navbar-nav-admin .nav-link:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
}

.navbar-nav-admin .nav-link::after {
    content: '';
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    width: 60%;
    height: 2px;
    background: #ffc107;
    transition: transform 0.3s ease;
}

.navbar-nav-admin .nav-link:hover::after,
.navbar-nav-admin .nav-link.active::after {
    transform: translateX(-50%) scaleX(1);
}

/* Active Link */
.navbar-nav-admin .nav-link.active {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.15);
    font-weight: 600;
}

/* Logout Link */
.nav-logout .nav-link {
    color: rgba(255, 255, 255, 0.75);
}

.nav-logout .nav-link:hover {
    color: #ff6b6b;
    background: rgba(255, 107, 107, 0.15);
}

.nav-logout .nav-link::after {
    background: #ff6b6b;
}

/* Navbar Container */
.navbar > .container-fluid {
    padding: 0 24px;
}

/* Mobile Toggler */
.navbar-toggler {
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 8px 12px;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Dropdown Menu (if needed in future) */
.dropdown-menu-admin {
    background: #2c3e50;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.dropdown-menu-admin .dropdown-item {
    color: rgba(255, 255, 255, 0.85);
    transition: all 0.2s ease;
}

.dropdown-menu-admin .dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

/* Responsive Design */
@media (max-width: 991px) {
    body {
        padding-top: 65px;
    }
    
    .admin-navbar {
        min-height: 65px;
    }
    
    .navbar-brand-admin {
        font-size: 17px;
    }
    
    .navbar-brand-admin img {
        height: 38px;
    }
    
    .admin-badge {
        display: block;
        margin-left: 0;
        margin-top: 5px;
        width: fit-content;
    }
    
    .navbar-nav-admin {
        margin-top: 10px;
        gap: 2px;
    }
    
    .navbar-nav-admin .nav-link {
        padding: 10px 15px;
        margin: 2px 0;
    }
    
    .navbar-nav-admin .nav-link::after {
        display: none;
    }
}

/* Notification Badge (for future use) */
.notification-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #dc3545;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 5px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}
</style>
</head>
<body>

<!-- Admin Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark admin-navbar fixed-top">
    <div class="container-fluid">
        <!-- Logo + Brand -->
        <a class="navbar-brand-admin" href="admin-dashboard.php">
            <img src="../image/logo.png" alt="Camiguin Rentals Logo">
            <span>TravelEase Camiguin<span class="admin-badge">Admin</span></span>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav navbar-nav-admin ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="add_tourist-spot.php">
                        <i class="fas fa-map-marked-alt"></i> Tourist Spots
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="view-tourist.php">
                        <i class="fas fa-users"></i> Tourists
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="view-user.php">
                        <i class="fas fa-user-shield"></i> Users
                    </a>
                </li>
                
                <li class="nav-item nav-logout">
                    <a class="nav-link" href="../users/landing-page.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
// Highlight current page in navigation
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav-admin .nav-link');
    
    navLinks.forEach(link => {
        // Remove active class from all links
        link.classList.remove('active');
        
        // Get the href path
        const linkPath = new URL(link.href).pathname;
        
        // Add active class to matching link
        if (currentPath.includes(linkPath.split('/').pop())) {
            link.classList.add('active');
        }
    });
});
</script>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>