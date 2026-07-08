<?php
session_start();
include "admin-header.php"; // Includes navbar and session check

// Admin info from session
$fullname       = $_SESSION['fullname'] ?? 'Admin';
$email          = $_SESSION['email'] ?? 'N/A';
$contact_number = $_SESSION['contact_number'] ?? 'N/A';
$username       = $_SESSION['username'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Camiguin Rentals</title>
<link rel="icon" href="../image/easeico.ico" type="image/x-icon"> 
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<style>
/* Professional Admin Dashboard */
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f8f9fa;
}

.dashboard-container {
    padding: 40px 0;
}

/* Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    border-radius: 12px;
    padding: 40px;
    margin-bottom: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.welcome-section h1 {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.welcome-section h1 i {
    margin-right: 12px;
    color: #ffc107;
}

.welcome-section p {
    font-size: 16px;
    color: #ecf0f1;
    margin: 0;
    letter-spacing: 0.3px;
}

/* Admin Info Card */
.admin-info-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 35px;
    margin-bottom: 40px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.admin-info-card h4 {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.admin-info-card h4 i {
    margin-right: 10px;
    color: #6c757d;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #2c3e50;
}

.info-item i {
    font-size: 24px;
    color: #2c3e50;
    margin-right: 15px;
    width: 30px;
    text-align: center;
}

.info-content strong {
    display: block;
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-content span {
    font-size: 15px;
    color: #2c3e50;
    font-weight: 500;
}

/* Section Header */
.section-header {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 25px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e9ecef;
}

.section-header i {
    margin-right: 10px;
    color: #6c757d;
}

/* Menu Cards */
.menu-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 0;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    height: 100%;
    overflow: hidden;
}

.menu-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
    border-color: #2c3e50;
}

.menu-card a {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 35px 20px;
    text-decoration: none;
    color: #2c3e50;
    height: 100%;
    min-height: 160px;
    position: relative;
    overflow: hidden;
}

.menu-card a::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.menu-card:hover a::before {
    transform: scaleX(1);
}

.menu-icon {
    font-size: 42px;
    margin-bottom: 15px;
    color: #2c3e50;
    transition: all 0.3s ease;
}

.menu-card:hover .menu-icon {
    transform: scale(1.1);
    color: #1a252f;
}

.menu-title {
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    letter-spacing: 0.3px;
    color: #2c3e50;
}

.menu-card:hover .menu-title {
    color: #1a252f;
}

/* Special Colors for Specific Cards */
.menu-card.card-primary .menu-icon { color: #3498db; }
.menu-card.card-primary:hover .menu-icon { color: #2980b9; }

.menu-card.card-success .menu-icon { color: #27ae60; }
.menu-card.card-success:hover .menu-icon { color: #229954; }

.menu-card.card-info .menu-icon { color: #16a085; }
.menu-card.card-info:hover .menu-icon { color: #138d75; }

.menu-card.card-warning .menu-icon { color: #f39c12; }
.menu-card.card-warning:hover .menu-icon { color: #d68910; }

.menu-card.card-purple .menu-icon { color: #8e44ad; }
.menu-card.card-purple:hover .menu-icon { color: #7d3c98; }

.menu-card.card-danger .menu-icon { color: #e74c3c; }
.menu-card.card-danger:hover .menu-icon { color: #c0392b; }
.menu-card.card-danger:hover a::before { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }

/* Statistics Overview (Optional for future) */
.stat-box {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 20px 0;
    }
    
    .welcome-section {
        padding: 25px;
    }
    
    .welcome-section h1 {
        font-size: 24px;
    }
    
    .admin-info-card {
        padding: 25px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .menu-card a {
        min-height: 140px;
        padding: 25px 15px;
    }
    
    .menu-icon {
        font-size: 36px;
    }
}
</style>
</head>
<body>

<div class="dashboard-container">
    <div class="container">

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1><i class="fas fa-user-shield"></i> Welcome, <?= htmlspecialchars($fullname) ?>!</h1>
            <p>Manage your Camiguin Rentals platform from this central dashboard</p>
        </div>

        <!-- Admin Info Card -->
        <div class="admin-info-card">
            <h4><i class="fas fa-id-card"></i> Administrator Information</h4>
            <div class="info-grid">
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <div class="info-content">
                        <strong>Username</strong>
                        <span><?= htmlspecialchars($username) ?></span>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div class="info-content">
                        <strong>Email Address</strong>
                        <span><?= htmlspecialchars($email) ?></span>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div class="info-content">
                        <strong>Contact Number</strong>
                        <span><?= htmlspecialchars($contact_number) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="section-header">
            <i class="fas fa-th-large"></i> Quick Actions
        </div>

        <div class="row">
            <!-- Dashboard Overview -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="menu-card card-primary">
                    <a href="admin-dashboard.php">
                        <div class="menu-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="menu-title">Dashboard Overview</div>
                    </a>
                </div>
            </div>

            <!-- Add Tourist Spot -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="menu-card card-success">
                    <a href="add_tourist-spot.php">
                        <div class="menu-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="menu-title">Add Tourist Spot</div>
                    </a>
                </div>
            </div>

            <!-- View Tourist Spots -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="menu-card card-purple">
                    <a href="view_tourist-spot.php">
                        <div class="menu-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="menu-title">View Tourist Spots</div>
                    </a>
                </div>
            </div>

            <!-- View Tourists -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="menu-card card-info">
                    <a href="view-tourist.php">
                        <div class="menu-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="menu-title">Manage Tourists</div>
                    </a>
                </div>
            </div>

            <!-- Tourist Spot Management -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="menu-card card-purple">
                    <a href="tourist-spot.php">
                        <div class="menu-icon">
                            <i class="fas fa-map"></i>
                        </div>
                        <div class="menu-title">Tourist Spot Details</div>
                    </a>
                </div>
            </div>

            <!-- View Users -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="menu-card card-warning">
                    <a href="view-user.php">
                        <div class="menu-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="menu-title">Manage Users</div>
                    </a>
                </div>
            </div>

            <!-- Logout -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="menu-card card-danger">
                    <a href="../users/landing-page.php">
                        <div class="menu-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <div class="menu-title">Logout</div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include("admin-footer.php"); ?>
</body>
</html>