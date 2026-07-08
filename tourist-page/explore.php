<?php
session_start();
include "../db.php";

// ✅ Check login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

// ✅ Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Get search inputs
$category_id = isset($_GET['category_id']) ? trim($_GET['category_id']) : '';
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : ''; // 'rental', 'tourist_spot', or empty for all
$availability_only = isset($_GET['available']) ? true : false;

// Array to hold all combined results
$combined_results = [];

// ================== Fetch Rentals ==================
if ($type_filter === '' || $type_filter === 'rental') {
    $rental_sql = "SELECT r.*, u.fullname AS provider_name, 'rental' AS type
                   FROM rentals r
                   JOIN users u ON r.provider_id = u.user_id
                   WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($category_id)) {
        $rental_sql .= " AND r.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }

    if ($availability_only) {
        $rental_sql .= " AND r.availability = 'available'";
    }

    if (!empty($searchQuery)) {
        $rental_sql .= " AND (r.title LIKE ? OR r.description LIKE ? OR r.city LIKE ? OR r.province LIKE ? OR r.country LIKE ? OR u.fullname LIKE ?)";
        $search_term = "%{$searchQuery}%";
        $params = array_merge($params, array_fill(0, 6, $search_term));
        $types .= str_repeat("s", 6);
    }

    $rental_sql .= " ORDER BY r.date_created DESC";
    $stmt = $conn->prepare($rental_sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rentals = $stmt->get_result();
    
    while ($rental = $rentals->fetch_assoc()) {
        $rental['item_type'] = 'rental';
        $combined_results[] = $rental;
    }
    $stmt->close();
}

// ================== Fetch Tourist Spots ==================
if ($type_filter === '' || $type_filter === 'tourist_spot') {
    $spot_sql = "SELECT ts.*, a.fullname AS admin_name, 'tourist_spot' AS type
                 FROM tourist_spot ts
                 LEFT JOIN admin a ON ts.admin_id = a.admin_id
                 WHERE 1=1";

    $spot_params = [];
    $spot_types = "";

    if (!empty($category_id)) {
        $spot_sql .= " AND ts.category = ?";
        $spot_params[] = $category_id;
        $spot_types .= "s";
    }

    if (!empty($searchQuery)) {
        $spot_sql .= " AND (ts.name LIKE ? OR ts.description LIKE ? OR ts.barangay LIKE ? OR ts.municipality LIKE ? OR ts.category LIKE ? OR a.fullname LIKE ?)";
        $spot_params = array_merge($spot_params, array_fill(0, 6, "%{$searchQuery}%"));
        $spot_types .= str_repeat("s", 6);
    }

    $spot_sql .= " ORDER BY ts.date_created DESC";
    $spot_stmt = $conn->prepare($spot_sql);
    if (!empty($spot_params)) $spot_stmt->bind_param($spot_types, ...$spot_params);
    $spot_stmt->execute();
    $tourist_spots = $spot_stmt->get_result();
    
    while ($spot = $tourist_spots->fetch_assoc()) {
        $spot['item_type'] = 'tourist_spot';
        $combined_results[] = $spot;
    }
    $spot_stmt->close();
}

// Sort combined results by date
usort($combined_results, function($a, $b) {
    $date_a = isset($a['date_created']) ? strtotime($a['date_created']) : 0;
    $date_b = isset($b['date_created']) ? strtotime($b['date_created']) : 0;
    return $date_b - $date_a;
});

?>

<?php include_once __DIR__ . "/tourist-header.php"; ?>

<style>
:root {
    --primary-color: #667eea;
    --primary-dark: #5568d3;
    --secondary-color: #764ba2;
    --success-color: #10b981;
    --background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
    --card-bg: #ffffff;
    --text-primary: #636363ff;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.2);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--background);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.main-container {
    padding: 60px 0;
}

/* Hero Section */
.hero-section {
    background: #c5c5c5ff;
    text-align: center;
    padding: 40px 20px;
    margin-bottom: 40px;
    animation: slideDown 0.6s ease-out;
}

.hero-section h2 {
    font-size: 3rem;
    font-weight: 800;
    color: white;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    letter-spacing: -1px;
    
}

.hero-subtitle {
    font-size: 1.2rem;
    color: rgba(113, 6, 135, 0.68);
    margin-bottom: 0;
}

/* Filter Card */
.filter-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: var(--shadow-lg);
    margin-bottom: 40px;
    animation: fadeInUp 0.8s ease-out;
}

.filter-grid {
    display: grid;
    grid-template-columns: 200px 1fr 200px auto;
    gap: 15px;
    align-items: center;
    margin-bottom: 20px;
}

.type-filter {
    display: flex;
    gap: 10px;
    padding-top: 10px;
}

.type-btn {
    flex: 1;
    padding: 10px 20px;
    border: 2px solid var(--border-color);
    background: white;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.type-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: #f8f9ff;
}

.type-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

.filter-grid select,
.filter-grid input {
    padding: 14px 18px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    color: var(--text-primary);
}

.filter-grid select:focus,
.filter-grid input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-search {
    padding: 14px 35px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

/* Results Header */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 0 10px;
}

.results-count {
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

/* Card Design */
.card-modern {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: var(--shadow-lg);
    transition: all 0.4s ease;
    animation: fadeIn 0.6s ease-out;
}

.card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
}

.card-layout {
    display: grid;
    grid-template-columns: 350px 1fr;
    min-height: 300px;
}

.card-image-wrapper {
    position: relative;
    overflow: hidden;
    background: #f0f0f0;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.card-modern:hover .card-image {
    transform: scale(1.08);
}

.card-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(0, 0, 0, 0.85);
    color: white;
    padding: 10px 18px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    backdrop-filter: blur(10px);
}

.badge-rental {
    background: linear-gradient(135deg, #10b981, #059669);
}

.badge-spot {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.card-content {
    padding: 30px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
    line-height: 1.3;
}

.card-location {
    color: var(--text-secondary);
    font-size: 15px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-location i {
    color: var(--primary-color);
    font-size: 16px;
}

.card-description {
    color: #555;
    line-height: 1.7;
    margin-bottom: 20px;
    font-size: 15px;
}

.rating-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 700;
    color: #f59e0b;
    margin-bottom: 20px;
}

.rating-count {
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 400;
}

.card-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-details, .btn-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.btn-details {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.btn-action {
    background: linear-gradient(135deg, var(--success-color), #059669);
}

.btn-details:hover, .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    color: white;
}

/* Comments Box */
.comments-box {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 20px;
    margin-top: 20px;
    border: 2px solid var(--border-color);
}

.comments-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.comments-title i {
    color: var(--primary-color);
    font-size: 16px;
}

.comment-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 12px;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.comment-card:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-md);
}

.comment-card:last-child {
    margin-bottom: 0;
}

.comment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.comment-author {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 14px;
}

.comment-rating {
    color: #f59e0b;
    font-weight: 600;
    font-size: 13px;
}

.comment-text {
    color: var(--text-secondary);
    line-height: 1.6;
    font-size: 14px;
}

.no-comments {
    color: var(--text-secondary);
    font-style: italic;
    text-align: center;
    padding: 12px;
    font-size: 14px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: white;
}

.empty-state i {
    font-size: 5rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 1.3rem;
    opacity: 0.9;
    margin-bottom: 20px;
}

.btn-reset {
    display: inline-block;
    padding: 12px 30px;
    background: white;
    color: var(--primary-color);
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 992px) {
    .hero-section h2 {
        font-size: 2.2rem;
    }

    .filter-grid {
        grid-template-columns: 1fr;
    }

    .type-filter {
        flex-direction: column;
    }

    .card-layout {
        grid-template-columns: 1fr;
    }

    .card-image-wrapper {
        height: 250px;
    }
}

@media (max-width: 576px) {
    .hero-section h2 {
        font-size: 1.8rem;
    }

    .filter-card {
        padding: 20px;
    }

    .card-content {
        padding: 20px;
    }

    .card-title {
        font-size: 1.4rem;
    }

    .card-actions {
        flex-direction: column;
    }

    .btn-details, .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="main-container">
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h2>Discover Paradise</h2>
            <p class="hero-subtitle">Find the perfect spots and rentals for your adventure</p>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" id="filterForm">
                <div class="filter-grid">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        <?php 
                        $categories->data_seek(0);
                        while ($cat = $categories->fetch_assoc()): 
                        ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input type="text" name="query" class="form-control" 
                           placeholder="🔍 Search destinations, rentals..." 
                           value="<?= htmlspecialchars($searchQuery) ?>">

                    <select name="available" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" <?= $availability_only ? 'selected' : '' ?>>Available Only</option>
                    </select>

                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>

                <!-- Type Filter Buttons -->
                <div class="type-filter">
                    <button type="button" class="type-btn <?= $type_filter === '' ? 'active' : '' ?>" 
                            onclick="setTypeFilter('')">
                        <i class="fas fa-globe"></i> All
                    </button>
                    <button type="button" class="type-btn <?= $type_filter === 'tourist_spot' ? 'active' : '' ?>" 
                            onclick="setTypeFilter('tourist_spot')">
                        <i class="fas fa-mountain"></i> Tourist Spots
                    </button>
                    <button type="button" class="type-btn <?= $type_filter === 'rental' ? 'active' : '' ?>" 
                            onclick="setTypeFilter('rental')">
                        <i class="fas fa-home"></i> Rentals & Services
                    </button>
                </div>

                <input type="hidden" name="type" id="typeInput" value="<?= htmlspecialchars($type_filter) ?>">
            </form>
        </div>

        <!-- Results Header -->
        <div class="results-header">
            <div class="results-count">
                <i class="fas fa-list"></i> <?= count($combined_results) ?> Results Found
            </div>
        </div>

        <!-- Combined Results -->
        <?php if (count($combined_results) > 0): ?>
            <?php foreach ($combined_results as $item): ?>
                <?php if ($item['item_type'] === 'tourist_spot'): ?>
                    <?php
                    // Get tourist spot image
                    $img_query = $conn->query("SELECT picture_path FROM spot_pictures WHERE spot_id='{$item['spot_id']}' LIMIT 1");
                    $img = ($img_query->num_rows > 0) ? $img_query->fetch_assoc()['picture_path'] : '../image/default.png';
                    ?>
                    <div class="card-modern">
                        <div class="card-layout">
                            <div class="card-image-wrapper">
                                <img src="<?= htmlspecialchars($img) ?>" class="card-image" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div class="card-badge badge-spot">
                                    <i class="fas fa-mountain"></i> Tourist Spot
                                </div>
                            </div>
                            <div class="card-content">
                                <div>
                                    <h4 class="card-title"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="card-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($item['municipality']) ?>, <?= htmlspecialchars($item['barangay']) ?>
                                    </p>
                                    <p class="card-description"><?= htmlspecialchars(substr($item['description'], 0, 150)) ?>...</p>
                                    
                                    <div class="card-actions">
                                        <a href="spot-details.php?spot_id=<?= $item['spot_id'] ?>" class="btn-details">
                                            <i class="fas fa-info-circle"></i> View Details
                                        </a>
                                    </div>

                                    <!-- Comments -->
                                    <div class="comments-box">
                                        <div class="comments-title">
                                            <i class="fas fa-comments"></i>
                                            Recent Visitor Reviews
                                        </div>
                                        <?php
                                        $review_query = $conn->prepare("SELECT s.comment, s.rating, u.fullname 
                                                                        FROM spot_reviews s
                                                                        JOIN users u ON s.tourist_id = u.user_id
                                                                        WHERE s.spot_id = ?
                                                                        ORDER BY s.date_created DESC LIMIT 2");
                                        $review_query->bind_param("s", $item['spot_id']);
                                        $review_query->execute();
                                        $reviews = $review_query->get_result();
                                        if ($reviews->num_rows > 0):
                                            while ($rev = $reviews->fetch_assoc()): ?>
                                                <div class="comment-card">
                                                    <div class="comment-header">
                                                        <span class="comment-author"><?= htmlspecialchars($rev['fullname']) ?></span>
                                                        <span class="comment-rating">⭐ <?= htmlspecialchars($rev['rating']) ?>/5</span>
                                                    </div>
                                                    <p class="comment-text"><?= htmlspecialchars($rev['comment']) ?></p>
                                                </div>
                                            <?php endwhile;
                                        else: ?>
                                            <p class="no-comments">No reviews yet. Be the first!</p>
                                        <?php endif; ?>
                                        <?php $review_query->close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: // Rental ?>
                    <?php
                    // Get rental image
                    $rental_imgs = $conn->query("SELECT image_path FROM rental_images WHERE rental_id={$item['id']} LIMIT 1");
                    $img = ($rental_imgs->num_rows > 0) ? $rental_imgs->fetch_assoc()['image_path'] : '../image/default.png';

                    // Get rating
                    $rate = $conn->query("SELECT AVG(rating) as avg, COUNT(*) as total FROM reviews WHERE rental_id={$item['id']}")->fetch_assoc();
                    $avg = $rate['avg'] ? round($rate['avg'], 1) : 0;
                    $count = $rate['total'];
                    ?>
                    <div class="card-modern">
                        <div class="card-layout">
                            <div class="card-image-wrapper">
                                <img src="<?= htmlspecialchars($img) ?>" class="card-image" alt="<?= htmlspecialchars($item['title']) ?>">
                                <div class="card-badge badge-rental">
                                    <i class="fas fa-home"></i> Rental
                                </div>
                            </div>
                            <div class="card-content">
                                <div>
                                    <h4 class="card-title"><?= htmlspecialchars($item['title']) ?></h4>
                                    <p class="card-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($item['city']) ?>, <?= htmlspecialchars($item['province']) ?>
                                    </p>
                                    <p class="card-description"><?= htmlspecialchars(substr($item['description'], 0, 130)) ?>...</p>
                                    
                                    <div class="rating-bar">
                                        ⭐ <?= $avg ?> / 5 
                                        <span class="rating-count">(<?= $count ?> reviews)</span>
                                    </div>

                                    <div class="card-actions">
                                        <a href="rental-details.php?rental_id=<?= $item['id'] ?>" class="btn-details">
                                            <i class="fas fa-info-circle"></i> View Details
                                        </a>
                                        <a href="rent.php?rental_id=<?= $item['id'] ?>" class="btn-action">
                                            <i class="fas fa-calendar-check"></i> <?= ($item['category_id'] >= 3 ? "Rent Now" : "Book Now") ?>
                                        </a>
                                    </div>

                                    <!-- Comments -->
                                    <div class="comments-box">
                                        <div class="comments-title">
                                            <i class="fas fa-comments"></i>
                                            Guest Reviews
                                        </div>
                                        <?php
                                        $r_query = $conn->prepare("SELECT r.comment, r.rating, u.fullname 
                                                                   FROM reviews r
                                                                   JOIN users u ON r.tourist_id = u.user_id
                                                                   WHERE r.rental_id = ?
                                                                   ORDER BY r.date_created DESC LIMIT 2");
                                        $r_query->bind_param("i", $item['id']);
                                        $r_query->execute();
                                        $r_comments = $r_query->get_result();
                                        if ($r_comments->num_rows > 0):
                                            while ($rev = $r_comments->fetch_assoc()): ?>
                                                <div class="comment-card">
                                                    <div class="comment-header">
                                                        <span class="comment-author"><?= htmlspecialchars($rev['fullname']) ?></span>
                                                        <span class="comment-rating">⭐ <?= htmlspecialchars($rev['rating']) ?>/5</span>
                                                    </div>
                                                    <p class="comment-text"><?= htmlspecialchars($rev['comment']) ?></p>
                                                </div>
                                            <?php endwhile;
                                        else: ?>
                                            <p class="no-comments">No reviews yet. Be the first!</p>
                                        <?php endif; ?>
                                        <?php $r_query->close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>No results found</p>
                <a href="booking.php" class="btn-reset">
                    <i class="fas fa-redo"></i> Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function setTypeFilter(type) {
    document.getElementById('typeInput').value = type;
    document.getElementById('filterForm').submit();
}
</script>

<?php include_once __DIR__ . "/tourist-footer.php"; ?>