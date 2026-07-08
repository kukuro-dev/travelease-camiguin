<?php
session_start();
include "../db.php";

// Redirect if not logged in OR not a provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== "provider") {
    header("Location: ../users/user-login.php");
    exit;
}

// Get user session data
$user_id   = $_SESSION['user_id'];
$fullname  = $_SESSION['fullname'];
$user_type = $_SESSION['user_type'];

// Include header
include __DIR__ . "/user-header.php";
?>

<!-- Dashboard Content -->
<div class="container-fluid py-5" style="background-color: #f8f9fa; min-height: 80vh;">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Welcome, <?php echo htmlspecialchars($fullname); ?>!</h1>
        <p class="lead">
            User ID: <strong><?php echo htmlspecialchars($user_id); ?></strong> | 
            Role: <strong>
                <?php echo ($user_type === "provider") ? "Business Owner" : htmlspecialchars($user_type); ?>
            </strong>
        </p>
    </div>

    <div class="row g-4 justify-content-center">

        <!-- My Account -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                    <h5 class="card-title fw-bold">My Account</h5>
                    <p class="card-text text-muted">Manage your personal information.</p>
                    <a href="MyAccount.php" class="btn btn-primary btn-sm mt-auto">Go</a>
                </div>
            </div>
        </div>

        <!-- Add Business -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-store fa-4x text-success mb-3"></i>
                    <h5 class="card-title fw-bold">Add Business</h5>
                    <p class="card-text text-muted">Register and manage your business.</p>
                    <a href="add-rentals.php" class="btn btn-success btn-sm mt-auto">Go</a>
                </div>
            </div>
        </div>

       <!-- Add Tourist Spot -->
<div class="col-md-4 col-lg-3">
    <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
        <div class="card-body text-center d-flex flex-column justify-content-center">
            <i class="fas fa-map-marked-alt fa-4x text-success mb-3"></i>
            <h5 class="card-title fw-bold">Add Tourist-Spot</h5>
            <p class="card-text text-muted">Register and manage your tourist spots.</p>
            <a href="add_tourist-spot.php" class="btn btn-success btn-sm mt-auto">Go</a>
        </div>
    </div>
</div>

        <!-- Bookings -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-calendar-check fa-4x text-info mb-3"></i>
                    <h5 class="card-title fw-bold">Bookings</h5>
                    <p class="card-text text-muted">View and manage bookings.</p>
                    <a href="bookings.php" class="btn btn-info btn-sm text-white mt-auto">Go</a>
                </div>
            </div>
        </div>

        <!-- Rentals -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-car-side fa-4x text-warning mb-3"></i>
                    <h5 class="card-title fw-bold">Rentals</h5>
                    <p class="card-text text-muted">View my rentals.</p>
                    <a href="view-rentals.php" class="btn btn-warning btn-sm text-white mt-auto">Go</a>
                </div>
            </div>
        </div>
        
<!-- Tourist-Spot -->
<div class="col-md-4 col-lg-3">
    <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
        <div class="card-body text-center d-flex flex-column justify-content-center">
            <i class="fas fa-map-marker-alt fa-4x text-warning mb-3"></i>
            <h5 class="card-title fw-bold">Tourist-Spot</h5>
            <p class="card-text text-muted">View my Tourist-Spots.</p>
            <a href="view_tourist-spot.php" class="btn btn-warning btn-sm text-white mt-auto">Go</a>
        </div>
    </div>
</div>

        <!-- Payment -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-credit-card fa-4x text-danger mb-3"></i>
                    <h5 class="card-title fw-bold">Payment</h5>
                    <p class="card-text text-muted">Manage your payments.</p>
                    <a href="payment.php" class="btn btn-danger btn-sm mt-auto">Go</a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-envelope fa-4x text-secondary mb-3"></i>
                    <h5 class="card-title fw-bold">Messages</h5>
                    <p class="card-text text-muted">Check your messages from customers.</p>
                    <a href="messages.php" class="btn btn-secondary btn-sm mt-auto">Go</a>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-star fa-4x text-warning mb-3"></i>
                    <h5 class="card-title fw-bold">Reviews</h5>
                    <p class="card-text text-muted">View customer reviews and feedback.</p>
                    <a href="reviews.php" class="btn btn-warning btn-sm text-white mt-auto">Go</a>
                </div>
            </div>
        </div>

        <!-- Logout -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-effect" style="height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-sign-out-alt fa-4x text-dark mb-3"></i>
                    <h5 class="card-title fw-bold">Logout</h5>
                    <p class="card-text text-muted">Sign out of your account securely.</p>
                    <a href="../users/logout.php" class="btn btn-dark btn-sm mt-auto">Go</a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Custom CSS for hover effect -->
<style>
.hover-effect {
    border-radius: 12px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.hover-effect:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}
</style>

<?php
// Include footer
include __DIR__ . "/user-footer.php";
?>
