<?php
session_start();
include "../db.php";

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['spot_id']) || empty($_GET['spot_id'])) {
    echo "<div class='alert alert-danger text-center mt-5'>❌ Tourist Spot not found.</div>";
    exit;
}

$spot_id = $_GET['spot_id'];
$tourist_id = $_SESSION['user_id'];

// Fetch Spot Details
$spot_stmt = $conn->prepare("SELECT * FROM tourist_spot WHERE spot_id = ?");
$spot_stmt->bind_param("s", $spot_id);
$spot_stmt->execute();
$spot = $spot_stmt->get_result()->fetch_assoc();
$spot_stmt->close();

if (!$spot) {
    echo "<div class='alert alert-danger text-center mt-5'>❌ Tourist Spot not found.</div>";
    exit;
}

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $insert = $conn->prepare("INSERT INTO spot_reviews (spot_id, tourist_id, rating, comment) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssis", $spot_id, $tourist_id, $rating, $comment);
        $insert->execute();
        $insert->close();
        header("Location: spot-details.php?spot_id=$spot_id&review=success");
        exit;
    } else {
        $error = "Please provide both rating and comment.";
    }
}

// Fetch Images
$images = $conn->prepare("SELECT picture_path FROM spot_pictures WHERE spot_id = ?");
$images->bind_param("s", $spot_id);
$images->execute();
$image_result = $images->get_result();
$images->close();

// Fetch Reviews
$reviews = $conn->prepare("SELECT sr.comment, sr.rating, sr.date_created, u.fullname 
                           FROM spot_reviews sr
                           JOIN users u ON sr.tourist_id = u.user_id
                           WHERE sr.spot_id = ?
                           ORDER BY sr.date_created DESC");
$reviews->bind_param("s", $spot_id);
$reviews->execute();
$review_result = $reviews->get_result();

// Calculate average rating
$avg_rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM spot_reviews WHERE spot_id = ?");
$avg_rating_stmt->bind_param("s", $spot_id);
$avg_rating_stmt->execute();
$rating_data = $avg_rating_stmt->get_result()->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'], 1);
$total_reviews = $rating_data['total_reviews'];
$avg_rating_stmt->close();
$reviews->close();
?>

<?php include_once __DIR__ . "/tourist-header.php"; ?>

<style>
/* Professional Spot Details Design */
.spot-details-container {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 100vh;
}

/* Back Button */
.back-btn {
    background: #ffffff;
    color: #495057;
    border: 1px solid #e0e0e0;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    margin-bottom: 25px;
}

.back-btn:hover {
    background: #f8f9fa;
    color: #2c3e50;
    border-color: #ced4da;
    transform: translateX(-3px);
}

/* Main Card */
.spot-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
}

/* Card Header */
.spot-card-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    padding: 30px 35px;
    position: relative;
}

.spot-title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 15px 0;
    letter-spacing: 0.5px;
}

.spot-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    margin-top: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    color: #ecf0f1;
}

.meta-item i {
    color: #ffc107;
    font-size: 18px;
}

.meta-item strong {
    color: #ffffff;
}

/* Rating Badge */
.rating-badge {
    position: absolute;
    top: 30px;
    right: 35px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 15px 20px;
    border-radius: 12px;
    text-align: center;
}

.rating-number {
    font-size: 36px;
    font-weight: 700;
    color: #ffc107;
    line-height: 1;
    margin-bottom: 5px;
}

.rating-stars {
    color: #ffc107;
    font-size: 18px;
}

.rating-count {
    font-size: 12px;
    color: #ecf0f1;
    margin-top: 5px;
}

/* Card Body */
.spot-card-body {
    padding: 35px;
}

.description-section {
    margin-bottom: 30px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #6c757d;
}

.description-text {
    font-size: 15px;
    color: #495057;
    line-height: 1.7;
}

/* Image Gallery */
.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.gallery-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.gallery-image:hover {
    transform: scale(1.05);
    border-color: #2c3e50;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.no-images {
    text-align: center;
    padding: 40px;
    color: #6c757d;
    font-size: 15px;
}

/* Reviews Section */
.reviews-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}

.reviews-header {
    background: #f8f9fa;
    padding: 25px 35px;
    border-bottom: 1px solid #e9ecef;
}

.reviews-header h4 {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.reviews-body {
    padding: 35px;
}

/* Alert */
.alert {
    border-radius: 8px;
    border: none;
    padding: 14px 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Review Item */
.review-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #e9ecef;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.reviewer-name {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #ffc107;
    font-size: 16px;
}

.review-comment {
    font-size: 14px;
    color: #495057;
    line-height: 1.6;
    margin-bottom: 10px;
}

.review-date {
    font-size: 12px;
    color: #6c757d;
}

.no-reviews {
    text-align: center;
    padding: 40px;
    color: #6c757d;
    font-size: 15px;
}

/* Add Review Form */
.add-review-section {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #e9ecef;
}

.add-review-title {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
    margin-bottom: 8px;
}

.form-select,
.form-control {
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.form-select:focus,
.form-control:focus {
    outline: none;
    border-color: #2c3e50;
    box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.1);
}

.btn-submit-review {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    border: none;
    padding: 12px 30px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
}

.btn-submit-review:hover {
    background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .spot-card-header,
    .spot-card-body,
    .reviews-header,
    .reviews-body {
        padding: 25px 20px;
    }
    
    .spot-title {
        font-size: 26px;
    }
    
    .rating-badge {
        position: static;
        margin-top: 20px;
        display: inline-block;
    }
    
    .spot-meta {
        gap: 15px;
    }
    
    .image-gallery {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    
    .gallery-image {
        height: 150px;
    }
}
</style>

<div class="spot-details-container">
    <div class="container">
        <a href="explore.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Explore
        </a>

        <!-- Spot Details Card -->
        <div class="spot-card">
            <div class="spot-card-header">
                <h1 class="spot-title"><?= htmlspecialchars($spot['name']) ?></h1>
                
                <div class="spot-meta">
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($spot['barangay']) ?>, <?= htmlspecialchars($spot['municipality']) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-ticket-alt"></i>
                        <strong>Entry Fee:</strong> ₱<?= htmlspecialchars($spot['maintenance_fee']) ?>
                    </div>
                </div>
                
                <?php if ($total_reviews > 0): ?>
                <div class="rating-badge">
                    <div class="rating-number"><?= $avg_rating ?></div>
                    <div class="rating-stars">
                        <?php for($i = 0; $i < 5; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-count"><?= $total_reviews ?> review<?= $total_reviews != 1 ? 's' : '' ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="spot-card-body">
                <!-- Description -->
                <div class="description-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i> About This Place
                    </h3>
                    <p class="description-text"><?= nl2br(htmlspecialchars($spot['description'])) ?></p>
                </div>

                <!-- Image Gallery -->
                <h3 class="section-title">
                    <i class="fas fa-images"></i> Photo Gallery
                </h3>
                <?php if ($image_result->num_rows > 0): ?>
                    <div class="image-gallery">
                        <?php while ($img = $image_result->fetch_assoc()): ?>
                            <img src="<?= htmlspecialchars($img['picture_path']) ?>" 
                                 class="gallery-image" 
                                 alt="Spot Image"
                                 onclick="openImageModal(this.src)">
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-images">
                        <i class="fas fa-image" style="font-size: 48px; margin-bottom: 10px;"></i>
                        <p>No images available for this location</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews Card -->
        <div class="reviews-card">
            <div class="reviews-header">
                <h4><i class="fas fa-comments"></i> Reviews & Ratings</h4>
            </div>
            
            <div class="reviews-body">
                <?php if (isset($_GET['review']) && $_GET['review'] === 'success'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Review added successfully!
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Reviews List -->
                <?php if ($review_result->num_rows > 0): ?>
                    <?php while ($rev = $review_result->fetch_assoc()): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="reviewer-name"><?= htmlspecialchars($rev['fullname']) ?></span>
                                <div class="review-rating">
                                    <?php for($i = 0; $i < $rev['rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    <?php for($i = $rev['rating']; $i < 5; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($rev['comment']) ?></p>
                            <span class="review-date">
                                <i class="far fa-clock"></i> <?= date("F d, Y", strtotime($rev['date_created'])) ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="fas fa-comment-slash" style="font-size: 48px; margin-bottom: 10px;"></i>
                        <p>No reviews yet. Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>

                <!-- Add Review Form -->
                <div class="add-review-section">
                    <h5 class="add-review-title">
                        <i class="fas fa-pen"></i> Write a Review
                    </h5>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select" required>
                                    <option value="">Select rating</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ Very Good</option>
                                    <option value="3">⭐⭐⭐ Good</option>
                                    <option value="2">⭐⭐ Fair</option>
                                    <option value="1">⭐ Poor</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience about this place..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn-submit-review">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; justify-content:center; align-items:center;" onclick="closeImageModal()">
    <img id="modalImage" style="max-width:90%; max-height:90%; border-radius:8px;">
    <button onclick="closeImageModal()" style="position:absolute; top:20px; right:20px; background:#ffffff; border:none; padding:10px 15px; border-radius:50%; cursor:pointer; font-size:20px;">
        <i class="fas fa-times"></i>
    </button>
</div>

<script>
function openImageModal(src) {
    document.getElementById('imageModal').style.display = 'flex';
    document.getElementById('modalImage').src = src;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}
</script>

<?php include_once __DIR__ . "/tourist-footer.php"; ?>