<?php
session_start();
include "../db.php";

// Check tourist login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

$tourist_id = $_SESSION['user_id'];

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $conn->query("UPDATE booking SET status = 'Cancelled' WHERE id = $booking_id AND tourist_id = '$tourist_id'");
    header("Location: my-bookings.php?cancelled=1");
    exit;
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'all';
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'latest';

// Fetch booking statistics
$stats_query = $conn->query("SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN booking_date >= CURDATE() AND status = 'approved' THEN 1 ELSE 0 END) as upcoming
    FROM booking WHERE tourist_id = '$tourist_id'");
$stats = $stats_query->fetch_assoc();

// Build query for bookings
$sql = "SELECT b.*, r.title, r.profile_image, r.city, r.province, r.category_id, c.name as category_name,
        p.status as payment_status, p.amount as payment_amount
    FROM booking b
    JOIN rentals r ON b.rental_id = r.id
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.tourist_id = '$tourist_id'";

// Apply filters
if ($status_filter !== 'all') {
    if ($status_filter === 'upcoming') {
        $sql .= " AND b.booking_date >= CURDATE() AND b.status = 'approved'";
    } elseif ($status_filter === 'past') {
        $sql .= " AND (b.booking_date < CURDATE() OR b.status IN ('Cancelled', 'Rejected'))";
    } else {
        $sql .= " AND b.status = '$status_filter'";
    }
}

if (!empty($search_query)) {
    $sql .= " AND (r.title LIKE '%$search_query%' OR r.city LIKE '%$search_query%')";
}

// Apply sorting
switch($sort_by) {
    case 'oldest':
        $sql .= " ORDER BY b.date_created ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY b.total_price DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY b.total_price ASC";
        break;
    default: // latest
        $sql .= " ORDER BY b.date_created DESC";
}

$bookings_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings - Camiguin Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="icon" href="../image/easeico.ico" type="image/x-icon">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    background: linear-gradient(135deg, #979798ff 0%, #dededfff 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    padding-bottom: 50px;
}

.bookings-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Page Header */
.page-header {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    margin-bottom: 30px;
    text-align: center;
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 10px;
}

.page-header p {
    color: #718096;
    font-size: 1.1rem;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-card.active {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 15px;
}

.stat-card:not(.active) .stat-icon.primary { background: linear-gradient(135deg, #4e73df, #224abe); color: white; }
.stat-card:not(.active) .stat-icon.warning { background: linear-gradient(135deg, #f6c23e, #dda20a); color: white; }
.stat-card:not(.active) .stat-icon.success { background: linear-gradient(135deg, #1cc88a, #13855c); color: white; }
.stat-card:not(.active) .stat-icon.danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
.stat-card:not(.active) .stat-icon.info { background: linear-gradient(135deg, #36b9cc, #258391); color: white; }

.stat-card.active .stat-icon {
    background: rgba(255,255,255,0.2);
    color: white;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* Filter Section */
.filter-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filter-header h4 {
    color: #2d3748;
    font-weight: 700;
    margin: 0;
}

.filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.filter-input, .filter-select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.filter-input:focus, .filter-select:focus {
    outline: none;
    border-color: #4e73df;
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.filter-btn, .clear-btn {
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.3s;
    white-space: nowrap;
}

.filter-btn {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
}

.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
}

.clear-btn {
    background: #ef4444;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

/* Booking Cards */
.booking-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: all 0.3s;
}

.booking-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.booking-header {
    display: flex;
    gap: 20px;
    padding: 20px;
    border-bottom: 2px solid #f7fafc;
}

.booking-img {
    width: 180px;
    height: 180px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.booking-main-info {
    flex: 1;
}

.booking-title-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.booking-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
}

.booking-id {
    background: #f7fafc;
    color: #4a5568;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
}

.category-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.booking-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #4a5568;
    font-size: 0.95rem;
}

.info-item i {
    color: #4e73df;
    width: 20px;
}

.booking-body {
    padding: 20px;
    background: #f7fafc;
}

.booking-details-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.detail-item {
    text-align: center;
    padding: 15px;
    background: white;
    border-radius: 10px;
}

.detail-label {
    font-size: 0.85rem;
    color: #718096;
    margin-bottom: 5px;
}

.detail-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
}

.status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-badge {
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-cancelled, .status-rejected { background: #fee2e2; color: #991b1b; }

.payment-badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
}

.payment-paid { background: #d1fae5; color: #065f46; }
.payment-not-paid { background: #fee2e2; color: #991b1b; }

.booking-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.3s;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-view {
    background: linear-gradient(135deg, #36b9cc, #258391);
    color: white;
}

.btn-review {
    background: linear-gradient(135deg, #f6c23e, #dda20a);
    color: white;
}

.btn-cancel {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Empty State */
.empty-state {
    background: white;
    border-radius: 15px;
    padding: 60px 40px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 5rem;
    color: #cbd5e0;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #2d3748;
    margin-bottom: 10px;
}

.empty-state p {
    color: #718096;
    margin-bottom: 25px;
}

.browse-btn {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    text-decoration: none;
    padding: 15px 30px;
    border-radius: 10px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.3s;
}

.browse-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(78, 115, 223, 0.4);
    color: white;
}

/* Alert */
.alert {
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
    border: none;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
}

/* Timeline Badge */
.timeline-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.timeline-upcoming {
    background: linear-gradient(135deg, #36b9cc, #258391);
    color: white;
}

.timeline-past {
    background: #e2e8f0;
    color: #4a5568;
}

/* Responsive */
@media (max-width: 1024px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .booking-details-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-header { padding: 25px; }
    .page-header h1 { font-size: 1.8rem; }
    .stats-grid { grid-template-columns: 1fr; }
    .booking-header { flex-direction: column; }
    .booking-img { width: 100%; height: 200px; }
    .booking-info-grid { grid-template-columns: 1fr; }
    .booking-details-grid { grid-template-columns: 1fr; }
    .booking-title-row { flex-direction: column; gap: 10px; }
    .status-row { flex-direction: column; gap: 15px; align-items: flex-start; }
}

/* Animation */
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

<?php include_once "tourist-header.php"; ?>

<div class="bookings-container">

    <!-- Page Header -->
    <div class="page-header fade-in">
        <h1><i class="fas fa-calendar-check"></i> My Bookings</h1>
        <p>Track and manage all your rental bookings</p>
    </div>

    <?php if(isset($_GET['cancelled'])): ?>
        <div class="alert alert-success fade-in">
            <i class="fas fa-check-circle"></i> Booking cancelled successfully!
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-grid fade-in">
        <a href="?status=all" class="stat-card <?= $status_filter === 'all' ? 'active' : '' ?>">
            <div class="stat-icon primary">
                <i class="fas fa-list"></i>
            </div>
            <div class="stat-value"><?= $stats['total_bookings'] ?></div>
            <div class="stat-label">All Bookings</div>
        </a>

        <a href="?status=upcoming" class="stat-card <?= $status_filter === 'upcoming' ? 'active' : '' ?>">
            <div class="stat-icon info">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <div class="stat-value"><?= $stats['upcoming'] ?></div>
            <div class="stat-label">Upcoming</div>
        </a>

        <a href="?status=Pending" class="stat-card <?= $status_filter === 'Pending' ? 'active' : '' ?>">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?= $stats['pending'] ?></div>
            <div class="stat-label">Pending</div>
        </a>

        <a href="?status=approved" class="stat-card <?= $status_filter === 'approved' ? 'active' : '' ?>">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?= $stats['approved'] ?></div>
            <div class="stat-label">Approved</div>
        </a>

        <a href="?status=past" class="stat-card <?= $status_filter === 'past' ? 'active' : '' ?>">
            <div class="stat-icon danger">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-value"><?= $stats['cancelled'] ?></div>
            <div class="stat-label">Past/Cancelled</div>
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section fade-in">
        <div class="filter-header">
            <h4><i class="fas fa-filter"></i> Filter & Search</h4>
        </div>
        <form method="get">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search Bookings</label>
                    <input type="text" name="search" class="filter-input" placeholder="Search by rental name or location..." value="<?= htmlspecialchars($search_query) ?>">
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-sort"></i> Sort By</label>
                    <select name="sort" class="filter-select">
                        <option value="latest" <?= $sort_by === 'latest' ? 'selected' : '' ?>>Latest First</option>
                        <option value="oldest" <?= $sort_by === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="price_high" <?= $sort_by === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="price_low" <?= $sort_by === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if(!empty($search_query) || $sort_by !== 'latest'): ?>
                        <a href="?status=<?= $status_filter ?>" class="clear-btn">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Bookings List -->
    <?php if($bookings_result->num_rows > 0): ?>
        <?php while($booking = $bookings_result->fetch_assoc()): 
            $is_upcoming = (strtotime($booking['booking_date']) >= strtotime('today') && $booking['status'] === 'approved');
            $is_past = (strtotime($booking['booking_date']) < strtotime('today') || in_array($booking['status'], ['Cancelled', 'Rejected']));
            $can_cancel = ($booking['status'] === 'Pending' || $booking['status'] === 'approved');
            $can_review = ($booking['status'] === 'approved' && strtotime($booking['booking_date']) < strtotime('today'));
        ?>
            <div class="booking-card fade-in">
                <div class="booking-header">
                    <img src="<?= htmlspecialchars($booking['profile_image']) ?>" class="booking-img" alt="<?= htmlspecialchars($booking['title']) ?>">
                    
                    <div class="booking-main-info">
                        <div class="booking-title-row">
                            <div>
                                <div class="booking-title"><?= htmlspecialchars($booking['title']) ?></div>
                                <span class="category-badge">
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($booking['category_name']) ?>
                                </span>
                            </div>
                            <div class="booking-id">
                                <i class="fas fa-hashtag"></i> <?= $booking['id'] ?>
                            </div>
                        </div>

                        <div class="booking-info-grid">
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?= htmlspecialchars($booking['city'] . ", " . $booking['province']) ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-user"></i>
                                <span><?= htmlspecialchars($booking['fullname']) ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <span><?= htmlspecialchars($booking['contact_number']) ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas <?= $booking['form_type'] === 'rent' ? 'fa-key' : 'fa-bed' ?>"></i>
                                <span><?= ucfirst($booking['form_type']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="booking-body">
                    <div class="booking-details-grid">
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-calendar"></i> Booking Date</div>
                            <div class="detail-value"><?= date('M d, Y', strtotime($booking['booking_date'])) ?></div>
                        </div>
                        
                        <?php if($booking['start_time']): ?>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-clock"></i> Start Time</div>
                            <div class="detail-value"><?= date('g:i A', strtotime($booking['start_time'])) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if($booking['end_time']): ?>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-clock"></i> End Time</div>
                            <div class="detail-value"><?= date('g:i A', strtotime($booking['end_time'])) ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-money-bill-wave"></i> Total Price</div>
                            <div class="detail-value" style="color: #4e73df;">₱<?= number_format($booking['total_price'], 2) ?></div>
                        </div>

                        <?php if($booking['guests'] > 0): ?>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-users"></i> Guests</div>
                            <div class="detail-value"><?= $booking['guests'] ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if($booking['units'] > 0): ?>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-cubes"></i> Units</div>
                            <div class="detail-value"><?= $booking['units'] ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if($booking['place_staying']): ?>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-hotel"></i> Place Staying</div>
                            <div class="detail-value" style="font-size: 0.9rem;"><?= htmlspecialchars($booking['place_staying']) ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-calendar-plus"></i> Booked On</div>
                            <div class="detail-value" style="font-size: 0.85rem;"><?= date('M d, Y', strtotime($booking['date_created'])) ?></div>
                        </div>
                    </div>

                    <div class="status-row">
                        <div>
                            <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                <i class="fas fa-circle"></i> <?= ucfirst($booking['status']) ?>
                            </span>
                            
                            <?php if($is_upcoming): ?>
                                <span class="timeline-badge timeline-upcoming">
                                    <i class="fas fa-arrow-circle-up"></i> Upcoming
                                </span>
                            <?php elseif($is_past): ?>
                                <span class="timeline-badge timeline-past">
                                    <i class="fas fa-history"></i> Past
                                </span>
                            <?php endif; ?>

                            <?php if($booking['payment_status']): ?>
                                <span class="payment-badge payment-<?= strtolower(str_replace(' ', '-', $booking['payment_status'])) ?>">
                                    <i class="fas fa-credit-card"></i> <?= $booking['payment_status'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="booking-actions">
                            <a href="booking-details.php?id=<?= $booking['id'] ?>" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>

                            <?php if($can_review): ?>
                                <?php 
                                // Check if already reviewed
                                $review_check = $conn->query("SELECT id FROM reviews WHERE booking_id = {$booking['id']} AND tourist_id = '$tourist_id'");
                                if($review_check->num_rows == 0):
                                ?>
                                <a href="add-review.php?booking_id=<?= $booking['id'] ?>" class="btn-action btn-review">
                                    <i class="fas fa-star"></i> Write Review
                                </a>
                                <?php else: ?><span class="btn-action" style="background: #d1fae5; color: #065f46; cursor: default;">
                                <i class="fas fa-check"></i> Reviewed
                            </span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($can_cancel): ?>
                            <button class="btn-action btn-cancel" onclick="confirmCancel(<?= $booking['id'] ?>)">
                                <i class="fas fa-times-circle"></i> Cancel Booking
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden cancel form -->
        <form id="cancelForm<?= $booking['id'] ?>" method="POST" style="display: none;">
            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
            <input type="hidden" name="cancel_booking" value="1">
        </form>

    <?php endwhile; ?>
<?php else: ?>
    <div class="empty-state fade-in">
        <i class="fas fa-calendar-times"></i>
        <h3>No Bookings Found</h3>
        <p>You haven't made any bookings yet. Start exploring amazing rentals!</p>
        <a href="booking.php" class="browse-btn">
            <i class="fas fa-search"></i> Browse Rentals
        </a>
    </div>
<?php endif; ?>
</div>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/fontawesome/js/all.min.js"></script>
<script>
function confirmCancel(bookingId) {
    if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
        document.getElementById('cancelForm' + bookingId).submit();
    }
}

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

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>
</body>
</html>