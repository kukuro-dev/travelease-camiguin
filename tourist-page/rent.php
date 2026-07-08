<?php
session_start();
include "../db.php";

// Check if tourist is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

$tourist_id = $_SESSION['user_id'];
$tourist = $conn->query("SELECT fullname, contact_number FROM users WHERE user_id='$tourist_id'")->fetch_assoc();

// Check rental_id
if (!isset($_GET['rental_id'])) {
    header("Location: booking.php");
    exit;
}
$rental_id = intval($_GET['rental_id']);

// Fetch rental info with category
$rental = $conn->query("
    SELECT r.*, c.name AS category_name, u.fullname AS provider_name 
    FROM rentals r
    JOIN categories c ON r.category_id = c.id
    JOIN users u ON r.provider_id = u.user_id
    WHERE r.id = $rental_id
")->fetch_assoc();

if (!$rental) die("Rental not found.");

// Fetch rental images
$images = $conn->query("SELECT * FROM rental_images WHERE rental_id=$rental_id");

// Determine label for form input
if ($rental['category_id'] == 1 || $rental['category_id'] == 2) {
    $countLabel = "How many guests?";
    $inputName = "guests";
} else {
    switch($rental['category_id']){
        case 3: $countLabel='How many snorkels to rent?'; break;
        case 4: $countLabel='How many bikes to rent?'; break;
        case 5: $countLabel='How many motors to rent?'; break;
        default: $countLabel='How many units to rent?';
    }
    $inputName = "units";
}

include_once "tourist-header.php";
?>

<style>
    :root {
        --primary-color: #5b5c5dff;
        --primary-dark: #464749ff;
        --secondary-color: #64748b;
        --success-color: #10b981;
        --background: #f8fafc;
        --card-bg: #ffffff;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    }

    body {
        background: var(--background);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: var(--text-primary);
    }

    .booking-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .breadcrumb-nav {
        background: transparent;
        padding: 0 0 1.5rem 0;
        margin: 0;
    }

    .breadcrumb-nav a {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }

    .breadcrumb-nav a:hover {
        color: var(--primary-color);
    }

    .breadcrumb-nav span {
        color: var(--text-primary);
        font-weight: 500;
    }

    .main-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 992px) {
        .main-grid {
            grid-template-columns: 1fr;
        }
    }

    .rental-card {
        background: var(--card-bg);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .rental-header {
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .rental-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.5rem 0;
        line-height: 1.2;
    }

    .category-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 0.375rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .provider-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .provider-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
    }

    .provider-details h6 {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .provider-details p {
        margin: 0;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .rental-body {
        padding: 2rem;
    }

    .rental-description {
        font-size: 1rem;
        line-height: 1.7;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .info-item {
        display: flex;
        align-items: start;
        gap: 0.75rem;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        flex-shrink: 0;
    }

    .info-content h6 {
        margin: 0 0 0.25rem 0;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        font-weight: 600;
    }

    .info-content p {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .image-gallery {
        padding: 0 2rem 2rem 2rem;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }

    .gallery-item {
        position: relative;
        aspect-ratio: 4/3;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .gallery-item:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .booking-form-card {
        background: var(--card-bg);
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        position: sticky;
        top: 2rem;
    }

    .form-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-radius: 16px 16px 0 0;
    }

    .form-header h5 {
        margin: 0 0 0.5rem 0;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .form-header .price-display {
        font-size: 1.875rem;
        font-weight: 800;
        margin: 0;
    }

    .form-header .price-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .form-body {
        padding: 1.5rem;
    }

    .form-section {
        margin-bottom: 1.5rem;
    }

    .form-section-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        font-size: 0.9375rem;
        transition: all 0.2s;
        background: var(--card-bg);
        color: var(--text-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-control:read-only {
        background: var(--background);
        color: var(--text-secondary);
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, var(--success-color), #059669);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: var(--shadow-md);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
    }

    .modal-body {
        padding: 0;
    }

    .modal-body img {
        width: 100%;
        display: block;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
    }

    .input-icon .form-control {
        padding-left: 2.75rem;
    }
</style>

<div class="booking-container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav">
        <a href="booking.php">← Back to Listings</a>
        <span> / <?= htmlspecialchars($rental['title']) ?></span>
    </nav>

    <div class="main-grid">
        <!-- Left Column: Rental Details -->
        <div>
            <div class="rental-card">
                <div class="rental-header">
                    <span class="category-badge"><?= htmlspecialchars($rental['category_name']) ?></span>
                    <h1 class="rental-title"><?= htmlspecialchars($rental['title']) ?></h1>
                    
                    <div class="provider-info">
                        <div class="provider-avatar">
                            <?= strtoupper(substr($rental['provider_name'], 0, 1)) ?>
                        </div>
                        <div class="provider-details">
                            <h6><?= htmlspecialchars($rental['provider_name']) ?></h6>
                            <p>Property Provider</p>
                        </div>
                    </div>
                </div>

                <div class="rental-body">
                    <div class="rental-description">
                        <?= nl2br(htmlspecialchars($rental['description'])) ?>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <h6>Location</h6>
                                <p><?= htmlspecialchars($rental['city'].", ".$rental['province']) ?></p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="info-content">
                                <h6>Price</h6>
                                <p>₱<?= number_format($rental['price'],2) ?></p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <h6>Rate</h6>
                                <p><?= ($rental['category_id']>=3 ? "Per Day" : "Per Night") ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Gallery -->
                <div class="image-gallery">
                    <div class="gallery-grid">
                        <?php if ($images->num_rows > 0): ?>
                            <?php while($img = $images->fetch_assoc()): ?>
                                <div class="gallery-item" onclick="showModal('<?= $img['image_path'] ?>')">
                                    <img src="<?= $img['image_path'] ?>" alt="Rental Image">
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="gallery-item">
                                <img src="../image/default.png" alt="No images">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Booking Form -->
        <div>
            <div class="booking-form-card">
                <div class="form-header">
                    <h5><?= ($rental['category_id']>=3 ? "Rent This Item" : "Book Your Stay") ?></h5>
                    <p class="price-display">₱<?= number_format($rental['price'],2) ?></p>
                    <p class="price-label"><?= ($rental['category_id']>=3 ? "per day" : "per night") ?></p>
                </div>

                <div class="form-body">
                    <form method="POST" action="rent_process.php">
                        <input type="hidden" name="rental_id" value="<?= $rental_id ?>">
                        <input type="hidden" name="form_type" value="<?= ($rental['category_id']>=3 ? "rent" : "book") ?>">

                        <!-- Tourist Info Section -->
                        <div class="form-section">
                            <div class="form-section-title">Your Information</div>
                            
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($tourist['fullname']) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($tourist['contact_number']) ?>" readonly>
                            </div>
                        </div>

                        <!-- Booking Details Section -->
                        <div class="form-section">
                            <div class="form-section-title">Booking Details</div>

                            <?php if($rental['category_id']>=3): // Rent form ?>
                                <div class="form-group">
                                    <label class="form-label">Pickup Location</label>
                                    <input type="text" name="place_staying" class="form-control" placeholder="Enter your location" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="datetime-local" name="start_time" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">End Time</label>
                                    <input type="datetime-local" name="end_time" class="form-control" required>
                                </div>
                            <?php else: // Book form ?>
                                <div class="form-group">
                                    <label class="form-label">Check-in Date</label>
                                    <input type="date" name="checkin_date" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Check-out Date</label>
                                    <input type="date" name="checkout_date" class="form-control" required>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="form-label"><?= $countLabel ?></label>
                                <input type="number" name="<?= $inputName ?>" class="form-control" min="1" value="1" required>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-check-circle"></i> <?= ($rental['category_id']>=3 ? "Confirm Rental" : "Confirm Booking") ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <img id="modalImg" src="" alt="Full size image" style="width: 100%;">
            </div>
        </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
function showModal(src) {
    document.getElementById("modalImg").src = src;
    var myModal = new bootstrap.Modal(document.getElementById('imgModal'));
    myModal.show();
}

// Set minimum dates to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.setAttribute('min', today);
    });

    const datetimeInputs = document.querySelectorAll('input[type="datetime-local"]');
    const now = new Date();
    const minDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    datetimeInputs.forEach(input => {
        input.setAttribute('min', minDateTime);
    });
});
</script>

<?php include_once "tourist-footer.php"; ?>