<?php
session_start();
include "../db.php";

// Check if tourist is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

// Include header/navbar
include_once "tourist-header.php";

// Fetch categories for sidebar filters
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Handle sidebar filters
$selected_categories = isset($_GET['categories']) ? $_GET['categories'] : [];
$availability_only = isset($_GET['available']) ? true : false;

$cat_filter = "";
if(!empty($selected_categories)){
    $cat_ids = implode(",", array_map('intval', $selected_categories));
    $cat_filter = "AND r.category_id IN ($cat_ids)";
}

$avail_filter = $availability_only ? "AND LOWER(r.availability)='available'" : "";

// Fetch rentals with provider info
$rentals = $conn->query("
    SELECT r.*, u.contact_number, c.name as category_name
    FROM rentals r
    JOIN users u ON r.provider_id = u.user_id
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE 1=1
    $cat_filter
    $avail_filter
    ORDER BY r.date_created DESC
");

$total_results = $rentals->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Rentals - Camiguin Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<style>
/* Professional Booking.com Style Design */
body { 
    background: #f5f5f5;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.booking-container {
    padding: 30px 0;
}

/* Page Header */
.page-header {
    background: #ffffff;
    padding: 25px 0;
    margin-bottom: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.results-count {
    font-size: 14px;
    color: #6c757d;
    margin-top: 5px;
}

/* Sidebar Filters */
.sidebar {
    background: #ffffff;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: sticky;
    top: 100px;
}

.sidebar h5 {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e9ecef;
}

.sidebar h6 {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 12px;
    margin-top: 20px;
}

.form-check {
    margin-bottom: 10px;
}

.form-check-input {
    margin-top: 0.3em;
    cursor: pointer;
}

.form-check-label {
    font-size: 14px;
    color: #495057;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #2c3e50;
    border-color: #2c3e50;
}

.btn-apply-filter {
    width: 100%;
    background: #2c3e50;
    color: #ffffff;
    border: none;
    padding: 10px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.btn-apply-filter:hover {
    background: #1a252f;
    transform: translateY(-1px);
}

/* Rental Card - Horizontal Layout */
.rental-card {
    background: #ffffff;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.rental-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.rental-card-wrapper {
    display: flex;
    min-height: 200px;
}

/* Image Section - Left */
.rental-image-section {
    flex: 0 0 280px;
    position: relative;
    overflow: hidden;
}

.rental-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.rental-card:hover .rental-image {
    transform: scale(1.05);
}

.category-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(44, 62, 80, 0.9);
    color: #ffffff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Content Section - Right */
.rental-content-section {
    flex: 1;
    padding: 20px 25px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.rental-title {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
    line-height: 1.3;
}

.rental-title:hover {
    color: #1a252f;
}

.rental-description {
    font-size: 14px;
    color: #6c757d;
    line-height: 1.6;
    max-height: 60px;
    overflow-y: auto;
    margin-bottom: 12px;
}

.rental-description::-webkit-scrollbar {
    width: 4px;
}

.rental-description::-webkit-scrollbar-thumb {
    background: #ced4da;
    border-radius: 2px;
}

.rental-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #495057;
}

.meta-item i {
    color: #6c757d;
    font-size: 14px;
}

/* Bottom Section */
.rental-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #e9ecef;
}

.price-section {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.price-label {
    font-size: 12px;
    color: #6c757d;
}

.price-amount {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
}

.price-period {
    font-size: 14px;
    color: #6c757d;
    font-weight: 400;
}

/* Status Badge */
.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-available {
    background: #d4edda;
    color: #155724;
}

.status-unavailable {
    background: #f8d7da;
    color: #721c24;
}

/* Book Button */
.btn-book {
    background: #28a745;
    color: #ffffff;
    border: none;
    padding: 12px 30px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 15px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-book:hover {
    background: #218838;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-book i {
    font-size: 16px;
}

/* Empty State */
.empty-state {
    background: #ffffff;
    border-radius: 8px;
    padding: 60px 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.empty-state i {
    font-size: 64px;
    color: #ced4da;
    margin-bottom: 20px;
}

.empty-state h4 {
    font-size: 20px;
    color: #495057;
    margin-bottom: 10px;
}

.empty-state p {
    font-size: 14px;
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 992px) {
    .sidebar {
        position: static;
        margin-bottom: 25px;
    }
}

@media (max-width: 768px) {
    .rental-card-wrapper {
        flex-direction: column;
    }
    
    .rental-image-section {
        flex: 0 0 220px;
        width: 100%;
    }
    
    .rental-content-section {
        padding: 20px;
    }
    
    .rental-footer {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .btn-book {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Browse Rentals</h1>
        <p class="results-count"><?= $total_results ?> properties found</p>
    </div>
</div>

<div class="container booking-container">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="sidebar">
                <h5><i class="fas fa-filter"></i> Filter Results</h5>
                <form method="GET">
                    <h6>Category</h6>
                    <?php
                    $categories->data_seek(0);
                    while($cat = $categories->fetch_assoc()): 
                        $checked = in_array($cat['id'], $selected_categories) ? 'checked' : '';
                    ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" id="cat<?= $cat['id'] ?>" <?= $checked ?>>
                            <label class="form-check-label" for="cat<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </label>
                        </div>
                    <?php endwhile; ?>

                    <h6>Availability</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="available" id="filterAvailable" <?= $availability_only ? 'checked' : '' ?>>
                        <label class="form-check-label" for="filterAvailable">
                            Show available only
                        </label>
                    </div>

                    <button type="submit" class="btn-apply-filter">
                        <i class="fas fa-check"></i> Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Rentals List -->
        <div class="col-lg-9">
            <?php if($total_results == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>No properties found</h4>
                    <p>Try adjusting your filters to see more results</p>
                </div>
            <?php else: ?>
                <?php while($rental = $rentals->fetch_assoc()):
                    $avail = strtolower(trim($rental['availability']));
                    $is_available = $avail === 'available';
                ?>
                <div class="rental-card">
                    <div class="rental-card-wrapper">
                        <!-- Image Section -->
                        <div class="rental-image-section">
                            <?php if(!empty($rental['profile_image']) && file_exists($rental['profile_image'])): ?>
                                <img src="<?= $rental['profile_image'] ?>" alt="<?= htmlspecialchars($rental['title']) ?>" class="rental-image">
                            <?php else: ?>
                                <img src="../image/default.png" alt="No image" class="rental-image">
                            <?php endif; ?>
                            
                            <?php if(!empty($rental['category_name'])): ?>
                                <div class="category-badge"><?= htmlspecialchars($rental['category_name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Content Section -->
                        <div class="rental-content-section">
                            <div>
                                <h3 class="rental-title"><?= htmlspecialchars($rental['title']) ?></h3>
                                <div class="rental-description"><?= htmlspecialchars($rental['description']) ?></div>
                                
                                <div class="rental-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($rental['city'].", ".$rental['province']) ?></span>
                                    </div>
                                    <?php if(!empty($rental['contact_number'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?= htmlspecialchars($rental['contact_number']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="rental-footer">
                                <div class="price-section">
                                    <span class="price-label">Starting from</span>
                                    <div>
                                        <span class="price-amount">₱<?= number_format($rental['price'], 2) ?></span>
                                        <span class="price-period">/ day</span>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <span class="status-badge status-<?= $is_available ? 'available' : 'unavailable' ?>">
                                        <?= $is_available ? 'Available' : 'Unavailable' ?>
                                    </span>
                                    
                                    <?php if($is_available): ?>
                                        <a href="rental-details.php?rental_id=<?= $rental['id'] ?>" class="btn-book">
                                            <i class="fas fa-arrow-right"></i> View Details
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/fontawesome/js/all.min.js"></script>
</body>
</html>