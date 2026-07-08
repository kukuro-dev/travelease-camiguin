<?php
session_start();
include "../db.php";

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['rental_id']) || empty($_GET['rental_id'])) {
    echo "<div class='alert alert-danger text-center mt-5'>❌ Rental not found.</div>";
    exit;
}

$rental_id = intval($_GET['rental_id']);
$tourist_id = $_SESSION['user_id'];

// Fetch Rental Info
$stmt = $conn->prepare("SELECT r.*, u.fullname, u.contact_number, u.email,
                        c.name as category_name
                        FROM rentals r 
                        JOIN users u ON r.provider_id = u.user_id 
                        LEFT JOIN categories c ON r.category_id = c.id
                        WHERE r.id = ?");
$stmt->bind_param("i", $rental_id);
$stmt->execute();
$rental = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rental) {
    echo "<div class='alert alert-danger text-center mt-5'>❌ Rental not found.</div>";
    exit;
}

// Handle review submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $insert = $conn->prepare("INSERT INTO reviews (rental_id, tourist_id, rating, comment) VALUES (?, ?, ?, ?)");
        $insert->bind_param("isis", $rental_id, $tourist_id, $rating, $comment);
        $insert->execute();
        $insert->close();
        header("Location: rental-details.php?rental_id=$rental_id&review=success");
        exit;
    } else {
        $error = "Please fill all fields correctly.";
    }
}

// Fetch Images
$images = $conn->prepare("SELECT image_path FROM rental_images WHERE rental_id=?");
$images->bind_param("i", $rental_id);
$images->execute();
$image_result = $images->get_result();
$images->close();

// Fetch Reviews
$reviews = $conn->prepare("SELECT r.comment, r.rating, r.date_created, u.fullname 
                           FROM reviews r
                           JOIN users u ON r.tourist_id = u.user_id
                           WHERE r.rental_id = ?
                           ORDER BY r.date_created DESC");
$reviews->bind_param("i", $rental_id);
$reviews->execute();
$review_result = $reviews->get_result();

// Calculate average rating
$avg_rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE rental_id = ?");
$avg_rating_stmt->bind_param("i", $rental_id);
$avg_rating_stmt->execute();
$rating_data = $avg_rating_stmt->get_result()->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'], 1);
$total_reviews = $rating_data['total_reviews'];
$avg_rating_stmt->close();
$reviews->close();
?>

<?php include_once __DIR__ . "/tourist-header.php"; ?>

<style>
/* Professional Rental Details Design */
.rental-details-container {
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
.rental-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
}

/* Card Header */
.rental-card-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    padding: 30px 35px;
    position: relative;
}

.rental-title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 15px 0;
    letter-spacing: 0.5px;
}

.rental-meta {
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

.price-badge {
    position: absolute;
    top: 30px;
    right: 35px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 15px 25px;
    border-radius: 12px;
    text-align: center;
}

.price-amount {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1;
}

.price-label {
    font-size: 12px;
    color: #ecf0f1;
    margin-top: 5px;
}

/* Rating Badge */
.rating-badge {
    position: static; /* no longer absolute */
    margin-top: 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}
.rating-number {
    font-size: 20px;
    font-weight: 700;
    color: #ffc107;
}
.rating-stars i {
    color: #ffc107;
    font-size: 16px;
}


/* Card Body */
.rental-card-body {
    padding: 35px;
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
    margin-bottom: 30px;
}

/* Image Gallery */
.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
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

/* Provider Info Card */
.provider-card {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    border-left: 4px solid #2c3e50;
}

.provider-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
}

.provider-info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 14px;
}

.provider-info-item i {
    width: 20px;
    font-size: 16px;
    color: #6c757d;
}

.provider-info-item strong {
    color: #2c3e50;
    margin-right: 8px;
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

/* Action Button (Book/Rent) */
.btn-action-primary {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #ffffff;
    border: none;
    padding: 16px 40px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 18px;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-action-primary:hover {
    background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    color: #ffffff;
}

.btn-action-primary i {
    font-size: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .rental-card-header,
    .rental-card-body,
    .reviews-header,
    .reviews-body {
        padding: 25px 20px;
    }
    
    .rental-title {
        font-size: 26px;
    }
    
    .price-badge,
    .rating-badge {
        position: static;
        margin-top: 15px;
        display: inline-block;
    }
    
    .rental-meta {
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

<div class="rental-details-container">
    <div class="container">
        <a href="explore.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Explore
        </a>

        <!-- Rental Details Card -->
        <div class="rental-card">
            <div class="rental-card-header">
                <h1 class="rental-title"><?= htmlspecialchars($rental['title']) ?></h1>
                
                <div class="rental-meta">
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>
                            <?= htmlspecialchars($rental['location_address']) ?>,
                            <?= htmlspecialchars($rental['city']) ?>,
                            <?= htmlspecialchars($rental['province']) ?>,
                            <?= htmlspecialchars($rental['country']) ?>
                        </span>
                    </div>
                    <?php if (!empty($rental['category_name'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span><?= htmlspecialchars($rental['category_name']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="price-badge">
                    <div class="price-amount">₱<?= number_format($rental['price'], 2) ?></div>
                    <div class="price-label">per day</div>
                </div>

                <?php if ($total_reviews > 0): ?>
                <div class="rating-badge" style="position: static; margin-top: 10px; display: inline-flex; gap: 8px;">
                    <span class="rating-number"><?= $avg_rating ?></span>
                    <div class="rating-stars">
                        <?php for($i = 0; $i < 5; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span style="color:#495057; font-size:14px; margin-left:5px;">(<?= $total_reviews ?> reviews)</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="rental-card-body">
                <!-- Description -->
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i> Description
                </h3>
                <p class="description-text"><?= nl2br(htmlspecialchars($rental['description'])) ?></p>

                <!-- Image Gallery -->
                <h3 class="section-title">
                    <i class="fas fa-images"></i> Photo Gallery
                </h3>
                <?php if ($image_result->num_rows > 0): ?>
                    <div class="image-gallery">
                        <?php while ($img = $image_result->fetch_assoc()): ?>
                            <img src="<?= htmlspecialchars($img['image_path']) ?>" 
                                 class="gallery-image" 
                                 alt="Rental Image"
                                 onclick="openImageModal(this.src)">
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-4">No images available</p>
                <?php endif; ?>

                <!-- Provider Info -->
                <h3 class="section-title">
                    <i class="fas fa-user-tie"></i> Provider Information
                </h3>
                <div class="provider-card">
                    <div class="provider-info-item">
                        <i class="fas fa-user"></i>
                        <span><strong>Name:</strong> <?= htmlspecialchars($rental['fullname']) ?></span>
                    </div>
                    <div class="provider-info-item">
                        <i class="fas fa-phone"></i>
                        <span><strong>Contact:</strong> <?= htmlspecialchars($rental['contact_number']) ?></span>
                    </div>
                    <?php if (!empty($rental['email'])): ?>
                    <div class="provider-info-item">
                        <i class="fas fa-envelope"></i>
                        <span><strong>Email:</strong> <?= htmlspecialchars($rental['email']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Book/Rent Button -->
                <div style="margin-top: 30px; text-align: center;">
                    <?php 
                    $category_name = strtolower($rental['category_name'] ?? '');
                    $button_text = 'Book Now';
                    $button_icon = 'fa-calendar-check';
                    
                    if (in_array($category_name, ['motorbike', 'rent bike', 'snorkeling', 'bike', 'motor', 'snorkel'])) {
                        $button_text = 'Rent Now';
                        $button_icon = 'fa-key';
                    } elseif (in_array($category_name, ['cottage', 'resort'])) {
                        $button_text = 'Book Now';
                        $button_icon = 'fa-calendar-check';
                    }
                    ?>
                    <a href="rent.php?rental_id=<?= $rental_id ?>" 
                       style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                              color: #ffffff;
                              border: none;
                              padding: 18px 50px;
                              border-radius: 8px;
                              font-weight: 700;
                              font-size: 18px;
                              text-decoration: none;
                              display: inline-flex;
                              align-items: center;
                              gap: 12px;
                              box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
                              transition: all 0.3s ease;
                              letter-spacing: 0.5px;"
                       onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(40, 167, 69, 0.4)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(40, 167, 69, 0.3)';">
                        <i class="fas <?= $button_icon ?>" style="font-size: 20px;"></i>
                        <span><?= $button_text ?></span>
                    </a>
                </div>
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
                            <textarea name="comment" class="form-control" rows="4" placeholder="Share your rental experience..." required></textarea>
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