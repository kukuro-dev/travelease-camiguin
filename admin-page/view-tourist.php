<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";
include "admin-header.php"; // Navbar + favicon

// Fetch only tourists
$tourists_res = $conn->query("SELECT * FROM users WHERE user_type='tourist' ORDER BY user_id ASC");
?>

<div class="container mt-5">
    <h2 class="mb-4">Tourists & Their Bookings/Rentals</h2>

    <?php while($tourist = $tourists_res->fetch_assoc()): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h4 class="card-title"><?= htmlspecialchars($tourist['fullname']) ?></h4>
                <p><strong>Contact:</strong> <?= htmlspecialchars($tourist['contact_number']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($tourist['address'] . ', ' . $tourist['city'] . ', ' . $tourist['province']) ?></p>

                <?php
                // Fetch bookings/rentals for this tourist
                $tourist_id = $tourist['user_id'];
                $booking_res = $conn->query("
                    SELECT b.*, r.title AS rental_title 
                    FROM booking AS b
                    LEFT JOIN rentals AS r ON b.rental_id = r.id
                    WHERE b.tourist_id='$tourist_id' 
                    ORDER BY b.date_created DESC
                ");

                $total_guests = 0;
                $total_units = 0;
                ?>

                <?php if($booking_res->num_rows > 0): ?>
                    <div class="mt-3">
                        <h5>Bookings / Rentals:</h5>
                        <?php while($booking = $booking_res->fetch_assoc()): 
                            // Count guests and units for this booking
                            $guests = (int)$booking['guests'];
                            $units = (int)$booking['units'];
                            $total_guests += $guests;
                            $total_units += $units;
                        ?>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <p><strong>Type:</strong> <?= htmlspecialchars($booking['form_type']) ?></p>
                                    <p><strong>Rental/Place:</strong> <?= htmlspecialchars($booking['rental_title'] ?? $booking['place_staying'] ?? 'N/A') ?></p>
                                    <p><strong>Total Price:</strong> ₱<?= htmlspecialchars($booking['total_price']) ?></p>
                                    <p><strong>Guests:</strong> <?= $guests ?> | <strong>Units:</strong> <?= $units ?></p>
                                    <p><strong>Booking Date:</strong> <?= htmlspecialchars($booking['booking_date']) ?></p>
                                    <p><strong>Start - End:</strong> <?= htmlspecialchars($booking['start_time']) ?> to <?= htmlspecialchars($booking['end_time']) ?></p>
                                    <p><strong>Status:</strong> <?= htmlspecialchars($booking['status']) ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <div class="alert alert-info mt-2">
                            <strong>Total Guests:</strong> <?= $total_guests ?> | 
                            <strong>Total Units/Rentals:</strong> <?= $total_units ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mt-2">No bookings or rentals yet.</p>
                <?php endif; ?>

            </div>
        </div>
    <?php endwhile; ?>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include("admin-footer.php"); ?>
