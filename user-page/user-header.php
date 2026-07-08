<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php"; // Database connection

// Check if user is logged in and is a provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== "provider") {
    header("Location: ../users/user-login.php"); // redirect to login if not logged in
    exit;
}

// Get user session data
$user_id   = $_SESSION['user_id'];
$fullname  = $_SESSION['fullname'];
$user_type = $_SESSION['user_type'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TravelEase Camiguin - Provider</title>

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="../image/easeico.ico">

<!-- CSS -->
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="stylesheet" href="../plugins/AdminLTE-4.0.0-rc4/dist/css/adminlte.min.css">

<style>
/* Professional Provider Navigation */
.provider-navbar {
    background: #ffffff;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    padding: 0;
}

/* Brand Section */
.navbar-brand-custom {
    display: flex;
    align-items: center;
    padding: 12px 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 18px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.navbar-brand-custom:hover {
    color: #1a252f;
    text-decoration: none;
}

.navbar-brand-custom img {
    height: 50px;
    margin-right: 12px;
    transition: transform 0.3s ease;
}

.navbar-brand-custom:hover img {
    transform: scale(1.05);
}

/* Navigation Links */
.navbar-nav-custom {
    gap: 5px;
}

.navbar-nav-custom .nav-link {
    color: #495057;
    font-weight: 500;
    font-size: 14.5px;
    padding: 12px 18px;
    border-radius: 4px;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
}

.navbar-nav-custom .nav-link i {
    margin-right: 6px;
    color: #6c757d;
    font-size: 14px;
}

.navbar-nav-custom .nav-link:hover {
    color: #2c3e50;
    background: #f8f9fa;
}

.navbar-nav-custom .nav-link:hover i {
    color: #2c3e50;
}

/* Active Link */
.navbar-nav-custom .nav-link.active {
    color: #2c3e50;
    background: #e9ecef;
    font-weight: 600;
}

.navbar-nav-custom .nav-link.active i {
    color: #2c3e50;
}

/* Logout Link Styling */
.nav-logout .nav-link {
    color: #6c757d;
}

.nav-logout .nav-link:hover {
    color: #dc3545;
    background: #fff5f5;
}

.nav-logout .nav-link i {
    color: inherit;
}

/* Provider Badge */
.provider-badge {
    display: inline-block;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 10px;
}

/* Navbar Container */
.navbar > .container-fluid {
    padding: 8px 24px;
}

/* Mobile Toggler */
.navbar-toggler {
    border: 1px solid #dee2e6;
    padding: 8px 12px;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.1);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(44, 62, 80, 0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Responsive Design */
@media (max-width: 991px) {
    .navbar-brand-custom img {
        height: 40px;
    }
    
    .navbar-brand-custom {
        font-size: 16px;
    }
    
    .provider-badge {
        display: block;
        margin-left: 0;
        margin-top: 5px;
        width: fit-content;
    }
    
    .navbar-nav-custom {
        margin-top: 15px;
        gap: 2px;
    }
    
    .navbar-nav-custom .nav-link {
        padding: 10px 15px;
    }
}

/* Current Page Indicator Script Support */
.navbar-nav-custom .nav-item.current-page .nav-link {
    color: #2c3e50;
    background: #e9ecef;
    font-weight: 600;
}
</style>
</head>
<body>

<!-- Provider Navbar -->
<nav class="navbar navbar-expand-lg provider-navbar">
    <div class="container-fluid">
        <!-- Logo + Brand -->
        <a class="navbar-brand-custom" href="user-dashboard.php">
            <img src="../image/logo.png" alt="TravelEase Camiguin">
            <span>TravelEase Camiguin<span class="provider-badge">Provider</span></span>
        </a>

        <!-- Toggle button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNavbar" aria-controls="providerNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu items -->
        <div class="collapse navbar-collapse" id="providerNavbar">
            <ul class="navbar-nav navbar-nav-custom ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="user-dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="MyAccount.php">
                        <i class="fas fa-user"></i> My Account
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="view-rentals.php">
                        <i class="fas fa-key"></i> My Rentals
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="bookings.php">
                        <i class="fas fa-calendar-check"></i> Bookings
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="payment.php">
                        <i class="fas fa-credit-card"></i> Payments
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="reviews.php">
                        <i class="fas fa-star"></i> Reviews
                    </a>
                </li>
                
                <li class="nav-item nav-logout">
                    <a class="nav-link" href="../users/logout.php">
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
    const navLinks = document.querySelectorAll('.navbar-nav-custom .nav-link');
    
    navLinks.forEach(link => {
        // Remove active class from all links first
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
<script src="../plugins/AdminLTE-4.0.0-rc4/dist/js/adminlte.min.js"></script>
</body>
</html>