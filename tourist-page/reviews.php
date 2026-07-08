<?php
session_start();
include "../db.php";

// Check tourist login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

$tourist_id = $_SESSION['user_id'];

// Handle delete review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = intval($_POST['review_id']);
    $conn->query("DELETE FROM reviews WHERE id = $review_id AND tourist_id = '$tourist_id'");
    header("Location: my-reviews.php?deleted=1");
    exit;
}

// Fetch statistics
$stats_query = $conn->query("SELECT 
    COUNT(*) as total_reviews,
    AVG(rating) as avg_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as four_plus
    FROM reviews WHERE tourist_id = '$tourist_id'");
$stats = $stats_query->fetch_assoc();

// Filter
$filter_rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch reviews with filters
$sql = "SELECT r.id AS review_id, r.rating, r.comment, r.date_created, 
           b.rental_id, b.form_type, b.total_price, b.booking_date,
           re.title AS rental_title, re.profile_image, c.name AS category_name
    FROM reviews r
    JOIN booking b ON r.booking_id = b.id
    JOIN rentals re ON b.rental_id = re.id
    JOIN categories c ON re.category_id = c.id
    WHERE r.tourist_id = '$tourist_id'";

if ($filter_rating > 0) {
    $sql .= " AND r.rating = $filter_rating";
}
if (!empty($search_query)) {
    $sql .= " AND (re.title LIKE '%$search_query%' OR r.comment LIKE '%$search_query%')";
}

$sql .= " ORDER BY r.date_created DESC";
$reviews = $conn->query($sql);

// Function to render stars
function renderStars($rating, $size = 'md') {
    $sizeClass = $size === 'lg' ? 'fa-lg' : ($size === 'sm' ? 'fa-sm' : '');
    $stars = "";
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star text-warning ' . $sizeClass . '"></i> ';
        } else {
            $stars .= '<i class="far fa-star text-warning ' . $sizeClass . '"></i> ';
        }
    }
    return $stars;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Reviews - Camiguin Rentals</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
<link rel="icon" href="../image/easeico.ico" type="image/x-icon">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    background: linear-gradient(135deg, #fcfdffff 0%, #b3b1b4ff 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    padding-bottom: 50px;
}

.reviews-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Header Section */
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
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 15px;
}

.stat-icon.primary { background: linear-gradient(135deg, #4e73df, #224abe); color: white; }
.stat-icon.success { background: linear-gradient(135deg, #1cc88a, #13855c); color: white; }
.stat-icon.warning { background: linear-gradient(135deg, #f6c23e, #dda20a); color: white; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.95rem;
    color: #718096;
}

/* Filter Section */
.filter-section {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    display: block;
    font-size: 0.95rem;
}

.filter-select, .filter-input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.filter-select:focus, .filter-input:focus {
    outline: none;
    border-color: #4e73df;
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
}

.filter-btn {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
}

.clear-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

/* Review Cards */
.review-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: all 0.3s;
}

.review-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.review-header {
    display: flex;
    gap: 20px;
    padding: 25px;
    border-bottom: 2px solid #f7fafc;
    align-items: center;
}

.review-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.review-info {
    flex: 1;
}

.rental-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
}

.category-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.booking-info {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    color: #718096;
    font-size: 0.9rem;
    margin-top: 10px;
}

.booking-info i {
    color: #4e73df;
}

.review-body {
    padding: 25px;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.rating-number {
    background: linear-gradient(135deg, #f6c23e, #dda20a);
    color: white;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1.1rem;
}

.review-comment {
    background: #f7fafc;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #4e73df;
    margin-bottom: 15px;
    line-height: 1.8;
    color: #2d3748;
}

.review-date {
    color: #718096;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.review-actions {
    display: flex;
    gap: 10px;
}

.btn-edit {
    background: linear-gradient(135deg, #36b9cc, #258391);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(54, 185, 204, 0.4);
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
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

/* Modal Styling */
.modal-content {
    border-radius: 15px;
    border: none;
}

.modal-header {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 20px 25px;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 2px solid #f7fafc;
}

.star-rating-selector {
    display: flex;
    gap: 10px;
    font-size: 2rem;
    margin: 10px 0;
}

.star-rating-selector i {
    cursor: pointer;
    transition: all 0.2s;
    color: #e2e8f0;
}

.star-rating-selector i:hover,
.star-rating-selector i.active {
    color: #f6c23e;
    transform: scale(1.1);
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

/* Responsive */
@media (max-width: 768px) {
    .page-header { padding: 25px; }
    .page-header h1 { font-size: 1.8rem; }
    .stats-grid { grid-template-columns: 1fr; }
    .review-header { flex-direction: column; text-align: center; }
    .review-img { width: 100%; height: 200px; }
    .filter-row { flex-direction: column; }
    .filter-group { width: 100%; }
    .review-actions { flex-direction: column; }
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

<div class="reviews-container">

    <!-- Page Header -->
    <div class="page-header fade-in">
        <h1><i class="fas fa-star"></i> My Reviews</h1>
        <p>View and manage all your rental reviews in one place</p>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="alert alert-success fade-in">
            <i class="fas fa-check-circle"></i> Review deleted successfully!
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['updated'])): ?>
        <div class="alert alert-success fade-in">
            <i class="fas fa-check-circle"></i> Review updated successfully!
        </div>
    <?php endif; ?>

    <!-- Stats Section -->
    <?php if($stats['total_reviews'] > 0): ?>
    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-comment-dots"></i>
            </div>
            <div class="stat-value"><?= $stats['total_reviews'] ?></div>
            <div class="stat-label">Total Reviews</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-value"><?= number_format($stats['avg_rating'], 1) ?></div>
            <div class="stat-label">Average Rating</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-thumbs-up"></i>
            </div>
            <div class="stat-value"><?= $stats['five_star'] ?></div>
            <div class="stat-label">5-Star Reviews</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value"><?= $stats['four_plus'] ?></div>
            <div class="stat-label">4+ Star Reviews</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="filter-section fade-in">
        <form method="get" class="filter-row">
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Search Reviews</label>
                <input type="text" name="search" class="filter-input" placeholder="Search by rental name or comment..." value="<?= htmlspecialchars($search_query) ?>">
            </div>

            <div class="filter-group">
                <label><i class="fas fa-star"></i> Filter by Rating</label>
                <select name="rating" class="filter-select">
                    <option value="0">All Ratings</option>
                    <option value="5" <?= $filter_rating == 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5 Stars)</option>
                    <option value="4" <?= $filter_rating == 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ (4 Stars)</option>
                    <option value="3" <?= $filter_rating == 3 ? 'selected' : '' ?>>⭐⭐⭐ (3 Stars)</option>
                    <option value="2" <?= $filter_rating == 2 ? 'selected' : '' ?>>⭐⭐ (2 Stars)</option>
                    <option value="1" <?= $filter_rating == 1 ? 'selected' : '' ?>>⭐ (1 Star)</option>
                </select>
            </div>

            <button type="submit" class="filter-btn">
                <i class="fas fa-filter"></i> Apply Filters
            </button>

            <?php if($filter_rating > 0 || !empty($search_query)): ?>
                <a href="my-reviews.php" class="clear-btn">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Reviews List -->
    <?php if($reviews->num_rows > 0): ?>
        <?php while($rev = $reviews->fetch_assoc()): ?>
            <div class="review-card fade-in">
                <div class="review-header">
                    <img src="<?= htmlspecialchars($rev['profile_image']) ?>" class="review-img" alt="<?= htmlspecialchars($rev['rental_title']) ?>">
                    <div class="review-info">
                        <div class="rental-title"><?= htmlspecialchars($rev['rental_title']) ?></div>
                        <span class="category-badge">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($rev['category_name']) ?>
                        </span>
                        <div class="booking-info">
                            <span><i class="fas fa-coins"></i> ₱<?= number_format($rev['total_price'], 2) ?></span>
                            <span><i class="fas fa-calendar-check"></i> <?= date('M d, Y', strtotime($rev['booking_date'])) ?></span>
                            <span><i class="fas <?= $rev['form_type']=='rent' ? 'fa-key' : 'fa-bed' ?>"></i> <?= $rev['form_type']=='rent' ? 'Rental' : 'Booking' ?></span>
                        </div>
                    </div>
                </div>

                <div class="review-body">
                    <div class="rating-display">
                        <div><?= renderStars($rev['rating'], 'lg') ?></div>
                        <div class="rating-number"><?= $rev['rating'] ?>/5</div>
                    </div>

                    <div class="review-comment">
                        <i class="fas fa-quote-left" style="color: #4e73df; margin-right: 10px;"></i>
                        <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                        <i class="fas fa-quote-right" style="color: #4e73df; margin-left: 10px;"></i>
                    </div>

                    <div class="review-date">
                        <i class="fas fa-clock"></i> Reviewed on <?= date('F d, Y \a\t g:i A', strtotime($rev['date_created'])) ?>
                    </div>

                    <div class="review-actions">
                        <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editReviewModal<?= $rev['review_id'] ?>">
                            <i class="fas fa-edit"></i> Edit Review
                        </button>
                        <button class="btn-delete" onclick="confirmDelete(<?= $rev['review_id'] ?>)">
                            <i class="fas fa-trash-alt"></i> Delete Review
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Review Modal -->
            <div class="modal fade" id="editReviewModal<?= $rev['review_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="update-review.php">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-edit"></i> Edit Review - <?= htmlspecialchars($rev['rental_title']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="review_id" value="<?= $rev['review_id'] ?>">

                                <div class="mb-4">
                                    <label class="form-label fw-bold"><i class="fas fa-star"></i> Rating</label>
                                    <select name="rating" class="form-select filter-select" required>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>" <?= $rev['rating'] == $i ? 'selected' : '' ?>>
                                                <?php for ($j = 1; $j <= 5; $j++): ?>
                                                    <?= $j <= $i ? '⭐' : '☆' ?>
                                                <?php endfor; ?> (<?= $i ?> Star<?= $i > 1 ? 's' : '' ?>)
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="fas fa-comment"></i> Your Review</label>
                                    <textarea name="comment" class="form-control filter-input" rows="5" placeholder="Share your experience..." required><?= htmlspecialchars($rev['comment']) ?></textarea>
                                    <small class="text-muted">Be specific and honest to help other tourists make informed decisions.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn-edit">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Form (hidden) -->
            <form id="deleteForm<?= $rev['review_id'] ?>" method="POST" style="display: none;">
                <input type="hidden" name="review_id" value="<?= $rev['review_id'] ?>">
                <input type="hidden" name="delete_review" value="1">
            </form>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state fade-in">
            <i class="fas fa-star-half-alt"></i>
            <h3>No Reviews Yet</h3>
            <p>You haven't written any reviews. Book a rental and share your experience!</p>
            <a href="booking.php" class="browse-btn">
                <i class="fas fa-search"></i> Browse Rentals
            </a>
        </div>
    <?php endif; ?>

</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/fontawesome/js/all.min.js"></script>

<script>
function confirmDelete(reviewId) {
    if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        document.getElementById('deleteForm' + reviewId).submit();
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