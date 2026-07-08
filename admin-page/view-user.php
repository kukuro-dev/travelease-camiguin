<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";
include "admin-header.php";

// Fetch providers only
$providers_res = $conn->query("SELECT * FROM users WHERE user_type='provider' ORDER BY user_id ASC");
?>

<div class="container mt-5">
    <h2 class="mb-4">Registered Providers & Their Rentals</h2>

    <?php while($provider = $providers_res->fetch_assoc()): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h4 class="card-title"><?= htmlspecialchars($provider['fullname']) ?></h4>
                <p><strong>Contact:</strong> <?= htmlspecialchars($provider['contact_number']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($provider['address'] . ', ' . $provider['city'] . ', ' . $provider['province']) ?></p>

                <?php
                // Fetch rentals for this provider
                $provider_id = $provider['user_id'];
                $rentals_res = $conn->query("SELECT * FROM rentals WHERE provider_id='$provider_id' ORDER BY date_created DESC");
                if($rentals_res->num_rows > 0):
                ?>
                    <div class="mt-3">
                        <h5>Businesses / Rentals:</h5>
                        <?php while($rental = $rentals_res->fetch_assoc()): ?>
                            <div class="card mb-2">
                                <div class="row g-0">
                                    <div class="col-md-3">
                                        <?php if(!empty($rental['profile_image']) && file_exists($rental['profile_image'])): ?>
                                            <img src="<?= $rental['profile_image'] ?>" class="img-fluid rounded-start" alt="Business Image">
                                        <?php else: ?>
                                            <div style="height:100%; background:#ccc; display:flex; align-items:center; justify-content:center;">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($rental['title']) ?></h6>
                                            <p class="card-text"><?= htmlspecialchars($rental['description']) ?></p>
                                            <p class="card-text"><strong>Price:</strong> ₱<?= htmlspecialchars($rental['price']) ?></p>
                                            <p class="card-text"><strong>Location:</strong> <?= htmlspecialchars($rental['location_address'] . ', ' . $rental['city'] . ', ' . $rental['province']) ?></p>
                                            <p class="card-text"><strong>Status:</strong> <?= htmlspecialchars($rental['availability']) ?></p>

                                            <!-- Display quantity, units, capacity, guests -->
                                            <p class="card-text">
                                                <?php if(!empty($rental['quantity'])): ?><strong>Quantity:</strong> <?= $rental['quantity'] ?> | <?php endif; ?>
                                                <?php if(!empty($rental['units'])): ?><strong>Units:</strong> <?= $rental['units'] ?> | <?php endif; ?>
                                                <?php if(!empty($rental['capacity'])): ?><strong>Capacity:</strong> <?= $rental['capacity'] ?> | <?php endif; ?>
                                                <?php if(!empty($rental['guests'])): ?><strong>Guests:</strong> <?= $rental['guests'] ?><?php endif; ?>
                                            </p>

                                            <a href="delete-rental.php?id=<?= $rental['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this rental?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mt-2">No rentals uploaded yet.</p>
                <?php endif; ?>

                <a href="delete-user.php?user_id=<?= $provider['user_id'] ?>" class="btn btn-danger mt-2" onclick="return confirm('Are you sure you want to delete this provider?')">
                    <i class="fas fa-trash"></i> Delete Provider
                </a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include("admin-footer.php"); ?>
