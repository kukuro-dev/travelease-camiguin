<?php
session_start();
include "../db.php";
include_once "tourist-header.php";

// Check tourist login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

// Get booking_id from URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if (!$booking_id) die("Booking not found.");

// Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, r.title AS rental_title, r.price AS rental_price, c.name AS category_name
    FROM booking b
    JOIN rentals r ON b.rental_id = r.id
    JOIN categories c ON r.category_id = c.id
    WHERE b.id = ? AND b.tourist_id = ?
");
$stmt->bind_param("is", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) die("Booking not found or does not belong to you.");

// Determine if rental or booking
$is_rental = in_array(strtolower($booking['category_name']), ['motorbike','motor','bike','snorkeling']);
$receipt_title = $is_rental ? "Rentals Receipt" : "Booking Receipt";

// Calculate duration
if ($is_rental) {
    $start_ts = strtotime($booking['start_time']);
    $end_ts = strtotime($booking['end_time']);
    $duration = max(1, ceil(($end_ts - $start_ts)/(24*3600))); // days
} else {
    $start_ts = strtotime($booking['start_time']);
    $end_ts = strtotime($booking['end_time']);
    $duration = max(1, ceil(($end_ts - $start_ts)/(24*3600))); // nights
}

// Total price
$total_price = floatval($booking['total_price']);
$price_per = floatval($booking['rental_price']);
$unit = $is_rental ? "day" : "night";
?>

<div class="container py-5" style="max-width:800px;">
    <div class="card shadow-sm p-4">
        <h2 class="text-center mb-4"><?= $receipt_title ?></h2>

        <p><i class="fas fa-id-badge"></i> <strong>Booking ID:</strong> <?= $booking['id'] ?></p>
        <p><i class="fas fa-user"></i> <strong>Tourist:</strong> <?= htmlspecialchars($booking['fullname']) ?></p>
        <p><i class="fas fa-store"></i> <strong>Rental:</strong> <?= htmlspecialchars($booking['rental_title']) ?> (<?= htmlspecialchars($booking['category_name']) ?>)</p>

        <?php if($is_rental): ?>
            <p><i class="fas fa-map-marker-alt"></i> <strong>Pickup Location:</strong> <?= htmlspecialchars($booking['place_staying']) ?></p>
            <p><i class="fas fa-clock"></i> <strong>Start Date/Time:</strong> <?= $booking['start_time'] ?></p>
            <p><i class="fas fa-clock"></i> <strong>End Date/Time:</strong> <?= $booking['end_time'] ?></p>
            <p><i class="fas fa-calendar-day"></i> <strong>Days:</strong> <?= $duration ?> day(s)</p>
        <?php else: ?>
            <p><i class="fas fa-calendar-check"></i> <strong>Check-in:</strong> <?= $booking['start_time'] ?></p>
            <p><i class="fas fa-calendar-times"></i> <strong>Check-out:</strong> <?= $booking['end_time'] ?></p>
            <p><i class="fas fa-bed"></i> <strong>Nights:</strong> <?= $duration ?></p>
            <p><i class="fas fa-users"></i> <strong>Guests:</strong> <?= $booking['guests'] ?? 1 ?></p>
        <?php endif; ?>

        <p><i class="fas fa-money-bill-wave"></i> <strong>Price per <?= $unit ?>:</strong> ₱<?= number_format($price_per,2) ?></p>
        <h4><i class="fas fa-coins"></i> Total Price: ₱<?= number_format($total_price,2) ?></h4>
        <p><i class="fas fa-info-circle"></i> <strong>Status:</strong> <?= $booking['status'] ?></p>

        <div class="mt-4 d-flex justify-content-between no-print">
            <button class="btn btn-success" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal"><i class="fas fa-star"></i> Add Review</button>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="add_review.php">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-star"></i> Leave a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                    <div class="mb-3">
                        <label>Rating (1-5)</label>
                        <select name="rating" class="form-select" required>
                            <option value="">Select</option>
                            <option value="1">1 - Poor</option>
                            <option value="2">2 - Fair</option>
                            <option value="3">3 - Good</option>
                            <option value="4">4 - Very Good</option>
                            <option value="5">5 - Excellent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Comment</label>
                        <textarea name="comment" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Review</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Later</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .navbar, .header { display:none !important; }
}
</style>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
