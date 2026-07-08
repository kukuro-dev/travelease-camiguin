<?php
session_start();
include "../db.php"; // Database connection

// Optional: check if tourist is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info - FIXED: use user_id instead of id
$user_query = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'");
$user = $user_query->fetch_assoc();

// Fetch statistics - FIXED: use 'booking' instead of 'bookings'
$upcoming_bookings_query = $conn->query("SELECT COUNT(*) as count FROM booking WHERE tourist_id = '$user_id' AND status = 'approved' AND booking_date >= CURDATE()");
$upcoming_bookings = $upcoming_bookings_query ? $upcoming_bookings_query->fetch_assoc()['count'] : 0;

// Check if favorites table exists, if not set to 0
$favorites_query = $conn->query("SHOW TABLES LIKE 'favorites'");
if ($favorites_query && $favorites_query->num_rows > 0) {
    $favorites_result = $conn->query("SELECT COUNT(*) as count FROM favorites WHERE user_id = '$user_id'");
    $favorites_count = $favorites_result ? $favorites_result->fetch_assoc()['count'] : 0;
} else {
    $favorites_count = 0;
}

$total_bookings_query = $conn->query("SELECT COUNT(*) as count FROM booking WHERE tourist_id = '$user_id'");
$total_bookings = $total_bookings_query ? $total_bookings_query->fetch_assoc()['count'] : 0;

$pending_bookings_query = $conn->query("SELECT COUNT(*) as count FROM booking WHERE tourist_id = '$user_id' AND status = 'Pending'");
$pending_bookings = $pending_bookings_query ? $pending_bookings_query->fetch_assoc()['count'] : 0;

// Fetch recent bookings - FIXED: use correct column names from booking table
$recent_bookings_res = $conn->query("SELECT b.*, r.title, r.profile_image, r.city, r.province 
    FROM booking b 
    JOIN rentals r ON b.rental_id = r.id 
    WHERE b.tourist_id = '$user_id' 
    ORDER BY b.date_created DESC 
    LIMIT 3");
$recent_bookings = [];
if ($recent_bookings_res) {
    while($row = $recent_bookings_res->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}

// Fetch 3 most recent available rentals for Popular Rentals
$popular_rentals_res = $conn->query("SELECT * FROM rentals WHERE availability='available' ORDER BY date_created DESC LIMIT 3");
$popular_rentals = [];
if ($popular_rentals_res) {
    while($row = $popular_rentals_res->fetch_assoc()) {
        $popular_rentals[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tourist Dashboard - Camiguin Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="stylesheet" href="../plugins/AdminLTE-4.0.0-rc4/dist/css/adminlte.min.css">
<link rel="icon" href="../image/easeico.ico" type="image/x-icon"> 
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { 
    background: linear-gradient(135deg, #ffffffff 0%, #6e6d6fff 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
}

.dashboard-container { 
    padding: 30px 20px; 
    max-width: 1400px; 
    margin: 0 auto;
}

/* Welcome Section */
.welcome-card {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.welcome-card h2 {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.welcome-card p {
    font-size: 1.1rem;
    line-height: 1.8;
    opacity: 0.95;
}

.user-name {
    color: #ffd700;
    font-weight: 600;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(180deg, #4e73df, #224abe);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 15px;
}

.stat-icon.primary { background: linear-gradient(135deg, #4e73df, #224abe); color: white; }
.stat-icon.success { background: linear-gradient(135deg, #1cc88a, #13855c); color: white; }
.stat-icon.warning { background: linear-gradient(135deg, #f6c23e, #dda20a); color: white; }
.stat-icon.info { background: linear-gradient(135deg, #36b9cc, #258391); color: white; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.95rem;
    color: #718096;
    font-weight: 500;
}

.stat-link {
    margin-top: 10px;
    display: inline-block;
    color: #4e73df;
    font-size: 0.9rem;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.stat-link:hover {
    color: #224abe;
    transform: translateX(5px);
}

/* Quick Actions */
.quick-actions {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.quick-actions h4 {
    font-size: 1.4rem;
    color: #2d3748;
    margin-bottom: 20px;
    font-weight: 700;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.action-btn {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.action-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(78, 115, 223, 0.4);
    color: white;
}

.action-btn i {
    font-size: 2rem;
}

.action-btn span {
    font-weight: 600;
    font-size: 0.95rem;
}

/* Recent Bookings */
.recent-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h4 {
    font-size: 1.4rem;
    color: #2d3748;
    font-weight: 700;
    margin: 0;
}

.view-all-link {
    color: #4e73df;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.view-all-link:hover {
    color: #224abe;
}

.booking-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.booking-item:hover {
    background: #f7fafc;
    border-color: #4e73df;
}

.booking-img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

.booking-details {
    flex: 1;
}

.booking-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
}

.booking-info {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 5px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-approved { background: #d1fae5; color: #065f46; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.empty-state {
    text-align: center;
    padding: 40px;
    color: #718096;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Popular Rentals Grid */
.rentals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.rental-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.rental-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.rental-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    position: relative;
}

.rental-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(78, 115, 223, 0.95);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.rental-body {
    padding: 20px;
}

.rental-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 10px;
}

.rental-description {
    font-size: 0.95rem;
    color: #718096;
    margin-bottom: 15px;
    height: 60px;
    overflow: hidden;

    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;

    /* Standard property for compatibility */
    line-clamp: 3;
}

.rental-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.rental-location {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
    color: #718096;
}

.rental-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: #4e73df;
}

.rental-price small {
    font-size: 0.85rem;
    font-weight: 400;
    color: #718096;
}

.book-btn {
    width: 100%;
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.book-btn:hover {
    background: linear-gradient(135deg, #224abe, #1a3a9e);
    transform: scale(1.02);
    color: white;
}

/* Support Section */
.support-card {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.support-card h4 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.support-info {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
}

.support-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
}

.support-item i {
    font-size: 1.3rem;
}

/* Explore Button */
.explore-section {
    text-align: center;
    margin: 40px 0;
}

.explore-btn {
    background: linear-gradient(135deg, #f6c23e, #dda20a);
    color: white;
    border: none;
    padding: 18px 50px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 8px 25px rgba(246, 194, 62, 0.4);
}

.explore-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(246, 194, 62, 0.5);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-container { padding: 20px 15px; }
    .welcome-card { padding: 25px; }
    .welcome-card h2 { font-size: 1.6rem; }
    .stats-grid { grid-template-columns: 1fr; }
    .action-grid { grid-template-columns: repeat(2, 1fr); }
    .rentals-grid { grid-template-columns: 1fr; }
    .booking-item { flex-direction: column; }
    .booking-img { width: 100%; height: 200px; }
}

/* Loading Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}
</style>
</head>
<body>

<?php include 'tourist-header.php'; ?>

<div class="dashboard-container">

    <!-- Welcome Section -->
    <div class="welcome-card fade-in">
        <h2>
            <i class="fas fa-hand-sparkles"></i>
            Welcome back, <span class="user-name"><?= htmlspecialchars($user['fullname'] ?? 'Guest') ?></span>!
        </h2>
        <p>
            Your personalized dashboard at <strong>Camiguin Rentals</strong> helps you explore, book, and manage your rentals seamlessly. Discover amazing places, track your trips, and enjoy hassle-free booking experiences.
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value"><?= $upcoming_bookings ?></div>
            <div class="stat-label">Upcoming Bookings</div>
            <a href="my-bookings.php" class="stat-link">View Details <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?= $pending_bookings ?></div>
            <div class="stat-label">Pending Approval</div>
            <a href="my-bookings.php?status=pending" class="stat-link">Check Status <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-heart"></i>
            </div>
            <div class="stat-value"><?= $favorites_count ?></div>
            <div class="stat-label">Saved Favorites</div>
            <a href="favorites.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="stat-value"><?= $total_bookings ?></div>
            <div class="stat-label">Total Bookings</div>
            <a href="my-bookings.php" class="stat-link">View History <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions fade-in">
        <h4><i class="fas fa-bolt"></i> Quick Actions</h4>
        <div class="action-grid">
            <a href="booking.php" class="action-btn">
                <i class="fas fa-search-location"></i>
                <span>Browse Rentals</span>
            </a>
            <a href="my-bookings.php" class="action-btn">
                <i class="fas fa-list-alt"></i>
                <span>My Bookings</span>
            </a>
            <a href="favorites.php" class="action-btn">
                <i class="fas fa-heart"></i>
                <span>Favorites</span>
            </a>
            <a href="profile.php" class="action-btn">
                <i class="fas fa-user-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="recent-section fade-in">
        <div class="section-header">
            <h4><i class="fas fa-history"></i> Recent Bookings</h4>
            <a href="my-bookings.php" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php if(count($recent_bookings) > 0): ?>
            <?php foreach($recent_bookings as $booking): ?>
                <div class="booking-item">
                    <img src="<?= htmlspecialchars($booking['profile_image']) ?>" class="booking-img" alt="<?= htmlspecialchars($booking['title']) ?>">
                    <div class="booking-details">
                        <div class="booking-title"><?= htmlspecialchars($booking['title']) ?></div>
                        <div class="booking-info">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['city'] . ", " . $booking['province']) ?>
                        </div>
                        <div class="booking-info">
                            <i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($booking['booking_date'])) ?>
                        </div>
                        <div class="booking-info">
                            <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No recent bookings found. Start exploring rentals now!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Customer Support -->
    <div class="support-card fade-in">
        <h4><i class="fas fa-headset"></i> 24/7 Customer Support</h4>
        <p>Need assistance? Our support team is here to help you with bookings, inquiries, and any concerns.</p>
        <div class="support-info">
            <div class="support-item">
                <i class="fas fa-phone"></i>
                <span>+63 912 345 6789</span>
            </div>
            <div class="support-item">
                <i class="fas fa-envelope"></i>
                <span>support@traveleasecamiguin.com</span>
            </div>
            <div class="support-item">
                <i class="fas fa-comments"></i>
                <span>Live Chat Available</span>
            </div>
        </div>
    </div>

    <!-- Explore Button -->
    <div class="explore-section fade-in">
        <a href="booking.php" class="explore-btn">
            <i class="fas fa-compass"></i>
            Explore More Rentals
        </a>
    </div>

    <!-- Popular Rentals -->
    <div class="recent-section fade-in">
        <div class="section-header">
            <h4><i class="fas fa-fire"></i> Popular Rentals</h4>
            <a href="booking.php" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="rentals-grid">
            <?php foreach($popular_rentals as $rental): ?>
                <div class="rental-card">
                    <div style="position: relative;">
                        <img src="<?= htmlspecialchars($rental['profile_image']) ?>" class="rental-img" alt="<?= htmlspecialchars($rental['title']) ?>">
                        <div class="rental-badge">Featured</div>
                    </div>
                    <div class="rental-body">
                        <div class="rental-title"><?= htmlspecialchars($rental['title']) ?></div>
                        <div class="rental-description"><?= htmlspecialchars($rental['description']) ?></div>
                        <div class="rental-meta">
                            <div class="rental-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?= htmlspecialchars($rental['city'] . ", " . $rental['province']) ?></span>
                            </div>
                        </div>
                        <div class="rental-price" style="margin-bottom: 15px;">
                            ₱<?= number_format($rental['price'], 2) ?>
                            <small>/day</small>
                        </div>
                        <a href="booking.php?rental_id=<?= $rental['id'] ?>" class="book-btn">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php include 'tourist-footer.php'; ?>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/AdminLTE-4.0.0-rc4/dist/js/adminlte.min.js"></script>
<script src="../assets/fontawesome/js/all.min.js"></script>

<script>
// Fade-in animation on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.5s ease-out';
    observer.observe(el);
});
</script>

</body>
</html>