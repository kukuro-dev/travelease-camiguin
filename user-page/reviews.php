<?php
session_start();
include "../db.php"; // Database connection

// Check if user is logged in as provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'provider') {
    header("Location: ../users/user-login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM reviews WHERE id = '$delete_id'");
    header("Location: provider-reviews.php"); // reload page
    exit;
}

// Fetch reviews for the provider's rentals
$reviews = $conn->query("
    SELECT r.*, u.fullname AS tourist_name, re.title AS rental_title
    FROM reviews r
    INNER JOIN users u ON r.tourist_id = u.user_id
    INNER JOIN rentals re ON r.rental_id = re.id
    WHERE re.provider_id = '$provider_id'
    ORDER BY r.date_created DESC
");

$total_reviews = $reviews->num_rows;

// Calculate average rating if there are reviews
$average_rating = 0;
$five_star_count = 0;
if ($total_reviews > 0) {
    $reviews_copy = $conn->query("
        SELECT r.rating
        FROM reviews r
        INNER JOIN rentals re ON r.rental_id = re.id
        WHERE re.provider_id = '$provider_id'
    ");
    $total_rating = 0;
    while ($r = $reviews_copy->fetch_assoc()) {
        $total_rating += $r['rating'];
        if ($r['rating'] == 5) {
            $five_star_count++;
        }
    }
    $average_rating = round($total_rating / $total_reviews, 1);
}
$five_star_percent = $total_reviews > 0 ? round(($five_star_count / $total_reviews) * 100) : 0;
?>

<?php include "user-header.php"; ?>

<style>
/* Provider Reviews Page Styling */
:root {
    --primary-dark: #4a4848ff;
    --secondary-dark: #2d2d2d;
    --accent-gray: #404040;
    --light-gray: #f5f5f5;
    --border-gray: #e0e0e0;
    --text-dark: #212529;
    --text-muted: #6c757d;
}

.reviews-page-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
    color: white;
    padding: 40px 0;
    margin: -20px -15px 40px -15px;
    box-shadow: 0 4px 20px rgba(76, 75, 75, 0.21);
}

.reviews-page-header h2 {
    font-weight: 700;
    font-size: 2.5rem;
    margin: 0;
}

.reviews-page-header i {
    color: #ffc107;
    margin-right: 15px;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(104, 103, 103, 0.39);
    border: 1px solid var(--border-gray);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(74, 73, 73, 0.28);
}

.stat-card .icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-card.total .icon {
    background: var(--primary-dark);
    color: white;
}

.stat-card.average .icon {
    background: var(--secondary-dark);
    color: #ffc107;
}

.stat-card.excellent .icon {
    background: var(--accent-gray);
    color: white;
}

.stat-card h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: var(--text-dark);
}

.stat-card p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 500;
}

.review-card {
    border: 1px solid var(--border-gray) !important;
    border-radius: 16px !important;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(122, 121, 121, 0.37) !important;
    position: relative;
}

.review-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-dark) 0%, var(--accent-gray) 100%);
    z-index: 1;
}

.review-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
}

.review-card .card-body {
    padding: 30px !important;
}

.delete-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #dc3545 !important;
    color: white !important;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    z-index: 10;
}

.delete-btn:hover {
    transform: rotate(90deg) scale(1.1);
    background: #bb2d3b !important;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

.rental-title {
    font-size: 1.3rem !important;
    font-weight: 700 !important;
    color: var(--primary-dark) !important;
    margin-bottom: 20px !important;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rental-title i {
    color: var(--secondary-dark);
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: var(--light-gray);
    border-radius: 12px;
    border-left: 4px solid var(--primary-dark);
    margin-bottom: 20px;
    position: relative;
}

.reviewer-info::before {
    content: 'REVIEWED BY';
    position: absolute;
    font-size: 0.7rem;
    color: var(--text-muted);
    font-weight: 600;
    letter-spacing: 0.5px;
    top: -8px;
    left: 15px;
    background: white;
    padding: 0 5px;
}

.reviewer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary-dark);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
    flex-shrink: 0;
}

.reviewer-name {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.reviewer-name i {
    color: var(--secondary-dark);
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.rating-badge {
    background: var(--primary-dark) !important;
    color: white !important;
    padding: 6px 15px !important;
    border-radius: 20px !important;
    font-weight: 600 !important;
}

.comment-section {
    background: var(--light-gray);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid var(--accent-gray);
    line-height: 1.7;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.comment-section i {
    color: var(--accent-gray);
    margin-right: 8px;
}

.date-display {
    color: var(--text-muted);
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.empty-state {
    background: white;
    border: 1px solid var(--border-gray);
    border-radius: 12px;
    padding: 60px 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.total-summary {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid var(--border-gray);
    text-align: center;
    margin-top: 30px;
}

.total-summary h5 {
    color: var(--primary-dark) !important;
    font-weight: 700 !important;
    font-size: 1.5rem !important;
    margin: 0;
}

@media (max-width: 768px) {
    .reviews-page-header h2 {
        font-size: 1.8rem;
    }
    
    .review-card .card-body {
        padding: 20px !important;
    }

    .reviewer-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<div class="reviews-page-header">
    <div class="container">
        <h2><i class="fas fa-star"></i> Customer Reviews</h2>
    </div>
</div>

<div class="container mt-4">
    
    <?php if($total_reviews == 0): ?>
        <div class="empty-state">
            <i class="fas fa-star"></i>
            <h4>No reviews yet</h4>
            <p>Customer reviews will appear here once they start coming in.</p>
        </div>
    <?php else: ?>
        
        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card total">
                <div class="icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h3><?= $total_reviews ?></h3>
                <p>Total Reviews</p>
            </div>
            <div class="stat-card average">
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3><?= $average_rating ?></h3>
                <p>Average Rating</p>
            </div>
            <div class="stat-card excellent">
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3><?= $five_star_percent ?>%</h3>
                <p>5-Star Reviews</p>
            </div>
        </div>

        <!-- Reviews Grid -->
        <div class="row">
            <?php while($rev = $reviews->fetch_assoc()): ?>
                <?php 
                    $rating_percent = round(($rev['rating'] / 5) * 100);
                    // Get initials for avatar
                    $name_parts = explode(' ', $rev['tourist_name']);
                    $initials = '';
                    foreach($name_parts as $part) {
                        if (!empty($part)) {
                            $initials .= strtoupper($part[0]);
                        }
                    }
                    $initials = substr($initials, 0, 2); // Max 2 letters
                ?>
                <div class="col-md-6 mb-4">
                    <div class="card review-card shadow-sm position-relative">
                        <!-- Delete Button -->
                        <a href="?delete_id=<?= $rev['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this review?')" 
                           class="delete-btn position-absolute top-0 end-0 m-3">
                            <i class="fas fa-times"></i>
                        </a>

                        <div class="card-body">
                            <!-- Rental Title -->
                            <h5 class="rental-title">
                                <i class="fas fa-store"></i> 
                                <?= htmlspecialchars($rev['rental_title']) ?>
                            </h5>
                            
                            <!-- Reviewer Info -->
                            <div class="reviewer-info">
                                <div class="reviewer-avatar"><?= $initials ?></div>
                                <div>
                                    <p class="reviewer-name">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($rev['tourist_name']) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Rating Display -->
                            <div class="rating-display">
                                <div>
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <?php if($i <= $rev['rating']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-warning"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="badge rating-badge"><?= $rev['rating'] ?>/5 (<?= $rating_percent ?>%)</span>
                            </div>

                            <!-- Comment -->
                            <div class="comment-section">
                                <i class="fas fa-quote-left"></i>
                                <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                            </div>

                            <!-- Date -->
                            <p class="date-display mb-0">
                                <i class="fas fa-calendar-alt"></i> 
                                <?= date("M d, Y", strtotime($rev['date_created'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Total Summary -->
        <div class="total-summary">
            <h5><i class="fas fa-chart-line"></i> Total Reviews: <?= $total_reviews ?></h5>
        </div>
        
    <?php endif; ?>
</div>

<?php include "user-footer.php"; ?>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>