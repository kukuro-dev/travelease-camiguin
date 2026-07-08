<?php
session_start();
include "../db.php";
include_once "tourist-header.php";

// Check tourist login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

$tourist_id = $_SESSION['user_id'];
$tourist = $conn->query("SELECT fullname, contact_number FROM users WHERE user_id='$tourist_id'")->fetch_assoc();

// Collect form data
$rental_id = intval($_POST['rental_id']);
$form_type = $_POST['form_type'] ?? 'rent'; // 'rent' for bikes/motors, 'book' for cottages/resorts

// Fetch rental info
$rental = $conn->query("SELECT * FROM rentals WHERE id=$rental_id")->fetch_assoc();
if (!$rental) die("Rental not found.");

// Determine rental vs booking
$is_rental = ($form_type === 'rent');
$receipt_title = $is_rental ? "Rentals Receipt" : "Booking Receipt";

$price = floatval($rental['price']);
$total_price = 0;
$duration = 0;
$status = 'Pending';

if ($is_rental) {
    // Rental logic (bike/motor/etc)
    $place_staying = $_POST['place_staying'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $units = intval($_POST['units'] ?? 1);

    $start_ts = strtotime($start_time);
    $end_ts = strtotime($end_time);
    $duration = max(1, ceil(($end_ts - $start_ts)/(24*3600))); // days

    $total_price = $price * $duration * $units;

    $stmt = $conn->prepare("
        INSERT INTO booking
        (rental_id, tourist_id, fullname, contact_number, form_type, place_staying, total_price, booking_date, start_time, end_time, units, status, date_created)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "isssssdisss",
        $rental_id,
        $tourist_id,
        $tourist['fullname'],
        $tourist['contact_number'],
        $form_type,
        $place_staying,
        $total_price,
        $start_time,
        $end_time,
        $units,
        $status
    );
    $stmt->execute();

} else {
    // Booking logic (cottage/resort)
    $checkin_date = $_POST['checkin_date'] ?? '';
    $checkout_date = $_POST['checkout_date'] ?? '';
    $guests = intval($_POST['guests'] ?? 1);

    $start_ts = strtotime($checkin_date);
    $end_ts = strtotime($checkout_date);
    $duration = max(1, ceil(($end_ts - $start_ts)/(24*3600))); // nights

    $total_price = $price * $duration * $guests;

    $stmt = $conn->prepare("
        INSERT INTO booking
        (rental_id, tourist_id, fullname, contact_number, form_type, total_price, booking_date, start_time, end_time, guests, status, date_created)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "issssdissis",
        $rental_id,
        $tourist_id,
        $tourist['fullname'],
        $tourist['contact_number'],
        $form_type,
        $total_price,
        $checkin_date,
        $checkout_date,
        $guests,
        $status
    );
    $stmt->execute();
}

// Get booking ID
$booking_id = $conn->insert_id;
?>

<div class="container py-5" style="max-width:800px;">
    <div class="card shadow-sm p-4">
        <h2 class="text-center mb-4"><?= $receipt_title ?></h2>
        <p><i class="fas fa-id-badge"></i> <strong>Booking ID:</strong> <?= $booking_id ?></p>
        <p><i class="fas fa-user"></i> <strong>Tourist:</strong> <?= htmlspecialchars($tourist['fullname']) ?></p>
        <p><i class="fas fa-store"></i> <strong>Rental:</strong> <?= htmlspecialchars($rental['title']) ?></p>

        <?php if($is_rental): ?>
            <p><i class="fas fa-map-marker-alt"></i> <strong>Pickup Location:</strong> <?= htmlspecialchars($place_staying) ?></p>
            <p><i class="fas fa-clock"></i> <strong>Start Date/Time:</strong> <?= $start_time ?></p>
            <p><i class="fas fa-clock"></i> <strong>End Date/Time:</strong> <?= $end_time ?></p>
            <p><i class="fas fa-calendar-day"></i> <strong>Days:</strong> <?= $duration ?> day(s)</p>
            <p><i class="fas fa-cubes"></i> <strong>Units Rented:</strong> <?= $units ?></p>
        <?php else: ?>
            <p><i class="fas fa-calendar-check"></i> <strong>Check-in:</strong> <?= $checkin_date ?></p>
            <p><i class="fas fa-calendar-times"></i> <strong>Check-out:</strong> <?= $checkout_date ?></p>
            <p><i class="fas fa-bed"></i> <strong>Nights:</strong> <?= $duration ?></p>
            <p><i class="fas fa-users"></i> <strong>Guests:</strong> <?= $guests ?></p>
        <?php endif; ?>

        <p><i class="fas fa-money-bill-wave"></i> <strong>Price per <?= $is_rental?'day':'night' ?>:</strong> ₱<?= number_format($price,2) ?></p>
        <h4><i class="fas fa-coins"></i> Total Price: ₱<?= number_format($total_price,2) ?></h4>
        <p><i class="fas fa-info-circle"></i> <strong>Status:</strong> <?= $status ?></p>
    </div>
</div>

<style>
@media print {
    .no-print, .navbar, .header { display:none !important; }
}
</style>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
